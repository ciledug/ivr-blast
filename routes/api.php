<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Campaign;
use App\Contact;
use App\Helpers\Helpers;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware'=>'client'], function(){

	// API Import Campaign Data
	Route::post('campaigns', function(Request $request){
		$input = $request->all(); // raw json
		
		$validator = Validator::make($input,[
			'data.*.account_id'=>'required',
			'data.*.phone'=>'required',
			'data.*.bill_date'=>'required|date_format:Y-m-d',
			'data.*.due_date'=>'required|date_format:Y-m-d',
			'data.*.nominal'=>'required|integer'
		]);
		
		if($validator->fails()){
			return response()->json([
				'status'=>false,
				'result'=>[
					'message'=>'Bad request',
					'error'=>$validator->errors()->all()
				]
			], Response::HTTP_BAD_REQUEST);
		}
		
		$sip = DB::connection('sip')
				->table('sip')
				->selectRaw('DISTINCT(id) as extension, data as callerid')
				->where('keyword', 'callerid')
				->where('id', 'like', env('SIP_PREFIX_EXT').'%')
				->orderBy(DB::raw('RAND()'))
				->get();
		
		
		if($sip->count() == 0){
			return response()->json([
				'status'=>false,
				'message'=>'Bad request, ext not available'
			], Response::HTTP_BAD_REQUEST);
		}
		
			
		// create campaign
		$batchId = time();
		$id_campaign = Campaign::create(['unique_key'=>$batchId, 'name'=>'Data API '.$batchId,'created_by'=>'Client API'])->id;
		
		// distributed campaign data
		$contacts = $request->data;
		$res = false;
		foreach(array_chunk($contacts, 2) as $rowsData){
			$dataContact = [];
			foreach($rowsData as $cd){
				$configVoice = [
					'bill_date' => $cd['bill_date'],
					'due_date' => $cd['due_date'],
					'nominal' => $cd['nominal']
				];
				
				$randomExt = rand(0,($sip->count()-1));
				
				$cd['campaign_id']	= $id_campaign;
				$cd['voice'] 		= Helpers::generateVoice($configVoice);
				$cd['extension'] 	= $sip[$randomExt]->extension;
				$cd['callerid'] 	= $sip[$randomExt]->callerid;
				
				array_push($dataContact, $cd);
			}
			
			$insert = Contact::insert($dataContact);
			if($insert){
				$res = true;
			}
		}
		
		if($res){
			Campaign::where('id', $id_campaign)->update(['total_data'=>count($contacts), 'status'=>1]);

			return response()->json([
				'status'=>true,
				'message'=>'success',
				'batch_id'=>$batchId
			], Response::HTTP_CREATED);
		}else{
			return response()->json([
				'status'=>false,
				'message'=>'Request timeout',
			], Response::HTTP_REQUEST_TIMEOUT);
		}

	});


	// API GET All Campaign Data
	Route::get('campaigns', function(Request $request){
		$limit 		= $request->limit ? $request->limit : 10;
		$startDate 	= $request->start_date ? $request->start_date : null;
		$endDate 	= $request->end_date ? $request->end_date : null;
		$sortColumn = $request->sort_column ? $request->sort_column : 'import_date';
		$sortType 	= $request->sort_type ? $request->sort_type : 'DESC';
		$search		= $request->search ? $request->search : null;
		
		$validator = Validator::make($request->all(),[
			'limit'=>['sometimes','required','integer',Rule::in([10,25,50,100])],
			'start_date'=>'required_with:end_date|date_format:Y-m-d',
			'end_date'=>'required_with:start_date|date_format:Y-m-d',
			'sort_column'=>['required_with:sort_type',Rule::in(["unique_key","status","total_data","called_data","remaining_data","progress_data","import_date"])],
			'sort_type'=>['required_with:sort_column',Rule::in(["asc","ASC","desc","DESC"])],
		]);
		
		if($validator->fails()){
			return response()->json([
				'status'=>false,
				'result'=>[
					'message'=>'Bad request',
					'error'=>$validator->errors()
				]
			], Response::HTTP_BAD_REQUEST);
		}
				
		$selectRaw = "c.unique_key as batch_id,
					  CASE c.status
						WHEN 0 THEN 'Ready'
						WHEN 1 THEN 'Running'
						WHEN 2 THEN 'Paused'
						ELSE 'Finished'
					  END AS `status`,
					  c.total_data,
					  CAST(SUM(IF(cd.total_calls > 0, 1, 0)) AS INT) AS called_data,
					  CAST(SUM(IF(cd.total_calls = 0, 1, 0)) AS INT) AS remaining_data,
					  CONCAT(ROUND(SUM(IF(cd.total_calls > 0, 1, 0)) / c.total_data * 100,2),'%') AS progress_data,
					  c.created_at AS import_date,
					  c.deleted_at";
		
		$subquery = DB::table('campaigns AS c')
				 ->leftJoin('contacts AS cd','c.id','=','cd.campaign_id')
				 ->selectRaw(trim(preg_replace('/\s+/', ' ', $selectRaw)))
				 ->groupBy('cd.campaign_id')
				 ->toSql();
		
		$query = DB::query()->from(DB::raw("({$subquery}) as sub"))
				->whereNull('deleted_at');
		
		if($search){
			$query = $query->where(function($query) use ($search){
				$columns = ['batch_id', 'status', 'total_data', 'called_data', 'remaining_data', 'progress_data', 'import_date'];
				$loop = 0;
	            foreach($columns as $column){
	                if($loop == 0){
	                    $query = $query->where($column, 'LIKE', '%'.$search.'%');
	                }else{
	                    $query = $query->orWhere($column, 'LIKE', '%'.$search.'%');
	                }

	                $loop++;
	            }
			});
		}
		
		if(!is_null($startDate) && !is_null($endDate)){
			$timespanStartDate 	= strtotime($startDate);
			$timespanEndDate 	= strtotime($endDate);
			if($timespanStartDate > $timespanEndDate){
				return response()->json([
					'status'=>false,
					'result'=>[
						'message'=>'Bad request',
						'error'=>'Start date must be lower than end date'
					]
				], Response::HTTP_BAD_REQUEST);
			}
			
			$query = $query->where(function($query) use ($startDate, $endDate){
                $query = $query->whereRaw('DATE(import_date) >= ? AND DATE(import_date) <= ?',[$startDate, $endDate]);
            });
		}
		
		$query = $query->orderBy($sortColumn, $sortType);
		if(!$request->limit){
			$query = $query->get()->map(function($attr){
				unset($attr->deleted_at);
				return $attr;
			});
			
			$batches = ['data'=>$query];
		}else{
			$batches = $query->paginate($limit)->withPath(route('api.campaigns').'?limit='.$limit);
			
			Collect($batches->items())->map(function($attr){
				unset($attr->deleted_at);
				return $attr;
			});
		}
		
		return response()->json([
			'status'=>true,
			'result'=>$batches
		], Response::HTTP_OK);
		
	})->name('api.campaigns');


	// API GET Campaign Detail
	Route::get('campaigns/{id}', function($id){
		$campaign = Campaign::where('unique_key', $id)->first();
		
		if(is_null($campaign)){
			return response()->json([
				'status'=>false,
				'result'=>'Campaign not found'
			], Response::HTTP_NOT_FOUND);
		}
		
		$selectRaw = "c.unique_key as batch_id,
					  CASE c.status
						WHEN 0 THEN 'Ready'
						WHEN 1 THEN 'Running'
						WHEN 2 THEN 'Paused'
						ELSE 'Finished'
					  END AS `status`,
					  c.total_data,
					  SUM(IF(cd.total_calls > 0, 1, 0)) AS called_data,
					  SUM(IF(cd.total_calls = 0, 1, 0)) AS remaining_data,
					  CONCAT(ROUND(SUM(IF(cd.total_calls > 0, 1, 0)) / c.total_data * 100,2),'%') AS progress_data,
					  c.created_at AS import_date";
					  
		$batch = DB::table('campaigns AS c')
				 ->leftJoin('contacts AS cd','c.id','=','cd.campaign_id')
				 ->selectRaw($selectRaw)
				 ->where('c.id', $campaign->id)
				 ->whereNull('c.deleted_at')
				 ->first();
		
		return response()->json([
			'status'=>true,
			'result'=>$batch
		], Response::HTTP_OK);
		
	});


	// API GET Campaign Data
	Route::get('campaigns/data/{id}', function(Request $request, $id){
		$campaign = Campaign::where('unique_key', $id)->first();
		
		if(is_null($campaign)){
			return response()->json([
				'status'=>false,
				'result'=>'Campaign not found'
			], Response::HTTP_NOT_FOUND);
		}
		
		$limit 		= $request->limit ? $request->limit : 10;
		$sortColumn = $request->sort_column ? $request->sort_column : 'name';
		$sortType 	= $request->sort_type ? $request->sort_type : 'ASC';
		$search		= $request->search ? $request->search : null;
		
		$validator = Validator::make($request->all(),[
			'limit'=>['sometimes','required','integer',Rule::in([1,10,25,50,100])],
			'sort_column'=>['required_with:sort_type',Rule::in(["account_id","name","nominal","call_dial"])],
			'sort_type'=>['required_with:sort_column',Rule::in(["asc","ASC","desc","DESC"])],
		]);
		
		if($validator->fails()){
			return response()->json([
				'status'=>false,
				'result'=>[
					'message'=>'Bad request',
					'error'=>$validator->errors()
				]
			], Response::HTTP_BAD_REQUEST);
		}
		
		$query = Contact::where('campaign_id',$campaign->id)
				 ->select('account_id','name','phone','bill_date','due_date','nominal','total_calls','call_dial AS call_date','call_response');
		
		if($search){
			$query = $query->where(function($query) use ($search){
				$columns = ['account_id', 'name', 'phone', 'bill_date', 'due_date', 'nominal', 'total_calls', 'call_dial', 'call_response'];
				$loop = 0;
	            foreach($columns as $column){
	                if($loop == 0){
	                    $query = $query->where($column, 'LIKE', '%'.$search.'%');
	                }else{
	                    $query = $query->orWhere($column, 'LIKE', '%'.$search.'%');
	                }

	                $loop++;
	            }
			});
		}
		
		$query = $query->orderBy($sortColumn, $sortType);
		
		if(!$request->limit){
			$contacts = ['data'=>$query->get()];
		}else{
			$contacts = $query->paginate($limit)->withPath(route('api.campaigns.data', $id).'?limit='.$limit);
		}
		
		return response()->json([
			'status'=>true,
			'result'=>$contacts
		], Response::HTTP_OK);
		
	})->name('api.campaigns.data');


	// API Delete Campaign
	Route::delete('campaigns/{id}', function($id){
		$campaign = Campaign::where('unique_key', $id)->first();
		if(is_null($campaign)){
			return response()->json([
				'status'=>false,
				'result'=>'Campaign not found'
			], Response::HTTP_NOT_FOUND);
		}
		
		$campaign->delete();
		return response()->json([
				'status'=>true,
				'result'=>'Campaign has been deleted'
			], Response::HTTP_OK);

	});


	// API Get All Call Data Records
	Route::get('cdr', function(Request $request){		
		$limit 		= $request->limit ? $request->limit : 10;
		$startDate 	= $request->start_date ? $request->start_date : null;
		$endDate 	= $request->end_date ? $request->end_date : null;
		$sortColumn = $request->sort_column ? $request->sort_column : 'call_dial';
		$sortType 	= $request->sort_type ? $request->sort_type : 'ASC';
		$search		= $request->search ? $request->search : null;
		
		$validator = Validator::make($request->all(),[
			'limit'=>['sometimes','required','integer',Rule::in([10,25,50,100])],
			'start_date'=>'required_with:end_date|date_format:Y-m-d',
			'end_date'=>'required_with:start_date|date_format:Y-m-d',
			'sort_column'=>['required_with:sort_type',Rule::in(["account_id","nominal","total_calls","call_dial","call_duration","call_response"])],
			'sort_type'=>['required_with:sort_column',Rule::in(["asc","ASC","desc","DESC"])]
		]);
		
		if($validator->fails()){
			return response()->json([
				'status'=>false,
				'result'=>[
					'message'=>'Bad Request',
					'error'=>$validator->errors()
				]
			], Response::HTTP_BAD_REQUEST);
		}
		
		$query = DB::table('call_logs AS cl')
				->leftJoin('contacts AS cd', 'cd.id','=','cl.contact_id')
				->select('cd.account_id','cd.name','cd.phone','cd.bill_date','cd.due_date','cd.nominal','cl.call_dial','cl.call_connect','cl.call_disconnect','cl.call_duration','cl.call_response',DB::raw('IF(cl.call_duration > 0, CONCAT("'.url('api/cdr/recording').'/",cl.id),NULL) AS call_recording'));
				
		if($search){
			$query = $query->where(function($query) use ($search){
				$columns = ['account_id', 'name', 'phone','bill_date','due_date','nominal','cl.call_dial','call_duration','cl.call_response'];
				$loop = 0;
	            foreach($columns as $column){
	                if($loop == 0){
	                    $query = $query->where($column, 'LIKE', '%'.$search.'%');
	                }else{
	                    $query = $query->orWhere($column, 'LIKE', '%'.$search.'%');
	                }

	                $loop++;
	            }
			});
		}
		
		if(!is_null($startDate) && !is_null($endDate)){
			$timespanStartDate 	= strtotime($startDate);
			$timespanEndDate 	= strtotime($endDate);
			if($timespanStartDate > $timespanEndDate){
				return response()->json([
					'status'=>false,
					'result'=>[
						'message'=>'Bad Request',
						'error'=>'Start date must be lower than end date'
					]
				], Response::HTTP_BAD_REQUEST);
			}
			
			$query = $query->where(function($query) use ($startDate, $endDate){
                $query = $query->whereRaw('DATE(cl.call_dial) >= ? AND DATE(cl.call_dial) <= ?',[$startDate, $endDate]);
            });
		}
		
		$query = $query->orderBy($sortColumn, $sortType);
		
		if(!$request->limit){
			$cdr = ['data'=>$query->get()];
		}else{
			$cdr = $query->paginate($limit)->withPath(route('api.cdr').'?limit='.$limit);
		}
		
		return response()->json([
			'status'=>true,
			'result'=>$cdr
		], Response::HTTP_OK);
		
	})->name('api.cdr');


	// API Get CDR based Campaign ID
	Route::get('cdr/campaign/{id}', function($id){
		$campaign = Campaign::where('unique_key', $id)->first();
		
		if(is_null($campaign)){
			return response()->json([
				'status'=>false,
				'result'=>'Campaign not found'
			], Response::HTTP_NOT_FOUND);
		}
		
		$cdr = DB::table('call_logs AS cl')
				->leftJoin('contacts AS cd', 'cd.id','=','cl.contact_id')
				->select('cd.account_id','cd.name','cd.phone','cd.bill_date','cd.due_date','cd.nominal','cl.call_dial','cl.call_connect','cl.call_disconnect','cl.call_duration','cl.call_response',DB::raw('IF(cl.call_duration > 0, CONCAT("'.url('api/cdr/recording').'/",cl.id),null) AS call_recording'))
				->where('cd.campaign_id', $campaign->id)
				->get();
		
		return response()->json([
			'status'=>true,
			'batch_id'=>$id,
			'data'=>$cdr
		], Response::HTTP_OK);
	
	});	


	// API Download Recording
	Route::get('cdr/recording/{id}', function($id){
		$getRecording = DB::table('call_logs')->where('id', $id)->first();
		if(is_null($getRecording) || is_null($getRecording->call_recording)){
			return response()->json([
				'status'=>Response::false,
				'result'=>'Recording not available'
			], Response::HTTP_NOT_FOUND);
		}
		
		$fileRecording = $getRecording->call_recording;
		
		$expFilename = explode('/',$fileRecording);
		$filename = $expFilename[count($expFilename) - 1];
		
		$checkpath  = '/var/spool/asterisk/monitor/'.$fileRecording;            
		if(file_exists($checkpath)){
			$filepath = $checkpath;
		}else{
			$filepath = '/mnt/disks/recording/'.$fileRecording;
		}
		
		$headers = [
		  'Content-Transfer-Encoding' => 'binary',
		  'Content-Type' => 'audio/wav',
		  'Content-Disposition' => 'attachment; filename="'.$filename.'"',
		  'Content-length' => filesize($filepath),
		  'Cache-Control' => 'no-cache, no-store, must-revalidate',
		  'Pragma' => 'no-cache',
		  'Expires' => '0',
		  'Accept-Ranges' => 'bytes'
		];
		
		return response()->download($filepath, $filename, $headers);
		
	})->name('api.cdr.recording');
});
