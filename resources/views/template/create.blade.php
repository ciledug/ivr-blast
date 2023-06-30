@include('layouts.include_page_header')
@include('layouts.include_sidebar')

@push('css')
<link href="{{ url('js/jquery-ui/jquery-ui.min.css') }}" rel="stylesheet">
@endpush

<style>
.disabled {
  background-color: #e9ecef;
  opacity: 1;
}
</style>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Templates</h1>
    <!--
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
        <li class="breadcrumb-item">Tables</li>
        <li class="breadcrumb-item active">General</li>
      </ol>
    </nav>
    -->
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">
              Add Template
            </h5>
            
            <form id="form-create-row-template" class="row g-3 needs-validation" method="POST" action="{{ route('templates.store') }}" enctype="multipart/form-data" novalidate>
              <div class="col-12">
                <div class="form-floating">
                  <input type="text" class="form-control" id="input-template-name" name="name" minlength="5" maxlength="20" placeholder="Template name" required>
                  <label for="input-template-name" class="form-label">Template Name (5-20 chars)</label>
                </div>
                @if ($errors->has('name'))
                <div class="invalid-feedback mt-2" style="display:block">
                    <strong>{{ $errors->first('name') }}</strong>
                </div>
                @endif
              </div>

              <div class="col-12 mt-5">
                <a href="#" id="btn-add-column" class="btn btn-primary btn-user-action">
                  Add Column
                </a>
              </div>

              <table id="table-header-list-container" class="table table-hover">
                <thead>
                  <tr>
                    <th>&nbsp;</th>
                    <th scope="col">Name</th>
                    <th scope="col">Column Type</th>
                    <th scope="col">Mandatory</th>
                    <th scope="col">Unique</th>
                    <th scope="col">Voice</th>
                    <th scope="col">Voice Position</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>

                <tbody>
                  @if(isset($template_headers) && count($template_headers) > 0)
                    @foreach($template_headers AS $keyHeader => $valHeader)
                      <tr id="row-template-data" class=".sortable-row">
                        <td>
                          <i class="bi bi-arrow-down-up">
                        </td>
                        <td>
                          <input class="form-control"  type="text" name="column_names[]" minlength="5" maxlength="30" value="{{ $valHeader->name }}" required>
                        </td>
                        <td>
                          <select name="column_types[]" class="form-select" required>
                            @foreach($column_types AS $keyColumn => $valColumn)
                            <option value="{{ $valColumn->type }}" @if ($valHeader->type === $valColumn->type) selected @endif>{{ $valColumn->name}}</option>
                            @endforeach
                          </select>
                        </td>
                        <td>
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input btn-radio-mandatories">
                            <input type="hidden" name="radio_mandatories[]" value="@if($valHeader->is_mandatory) echo 'on' @endif">
                          </div>
                        </td>
                        <td>
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input btn-radio-uniques">
                            <input type="hidden" name="radio_uniques[]" value="@if($valHeader->is_unique) echo 'on' @endif">
                          </div>
                        </td>
                        <td>
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input btn-radio-voices">
                            <input type="hidden" name="radio_voices[]" value="@if($valHeader->is_voice) echo 'on' @endif">
                          </div>
                        </td>
                        <td>
                          <input class="form-control"  type="number" name="voice_positions[]" min="1" max="15" value="{{ $valColumn->voice_position }}" readonly>
                        </td>
                        <td>
                          <button type="button" class="btn btn-danger btn-remove-column">X</button>
                        </td>
                      </tr>
                    @endforeach
                  @else
                    <tr id="row-no-template-data">
                      <td colspan="8" class="text-center">There's no templates data</td>
                    </tr>
                  @endif
                </tbody>
              </table>

              <div class="col-md-12 mt-4">
                <button type="button" class="btn btn-secondary btn-back">Cancel</button>
                <button type="submit" class="btn btn-outline-primary disabled" id="btn-submit-template">Save</button>&nbsp;
                {{ csrf_field() }}
                
                <div id="submit-spinner-save-template" class="spinner-border spinner-border-sm text-primary spinner-progress" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <span id="submit-spinner-save-template-text" class="spinner-progress">&nbsp;This may take a moment. Please wait...</span>
              </div>
              
            </form>
          </div>
        </div>

      </div>
    </div>
  </section>
</main>

@push('javascript')
<script src="{{ url('js/jquery-ui/jquery-ui.min.js') }}"></script>
<script type="text/javascript">

  var typeOptions = '<option value=""></option>@foreach($column_types AS $keyColumn => $valColumn)<option value="{{ $valColumn->type }}">{{ $valColumn->name}}</option>@endforeach';
  var headerCount = @php echo isset($template_headers) ? count($template_headers) : '0'; @endphp;
  var voiceSequenceList = [];

  function addColumn() {
    var tempRow = '<tr>';
    tempRow += '  <td>';
    tempRow += '    <i class="bi bi-arrow-down-up">';
    tempRow += '  </td>';
    tempRow += '  <td><input type="text" name="column_names[]" class="form-control" minlength="4" maxlength="20" required></td>';
    tempRow += '  <td>';
    tempRow += '    <select name="column_types[]" class="form-select" required>' + typeOptions + '</select>';
    tempRow += '  </td>';
    tempRow += '  <td class="align-middle">';
    tempRow += '    <div class="form-check">';
    tempRow += '      <input type="checkbox" class="form-check-input btn-radio-mandatories" onclick="setInputVoicePosition($(this), false)">';
    tempRow += '      <input type="hidden" name="radio_mandatories[]" value="">';
    tempRow += '    </div>';
    tempRow += '  </td>';
    tempRow += '  <td class="align-middle">';
    tempRow += '    <div class="form-check">';
    tempRow += '      <input type="checkbox" class="form-check-input btn-radio-uniques" onclick="setInputVoicePosition($(this), false)">';
    tempRow += '      <input type="hidden" name="radio_uniques[]" value="">';
    tempRow += '    </div>';
    tempRow += '  </td>';
    tempRow += '  <td class="align-middle">';
    tempRow += '    <div class="form-check">';
    tempRow += '      <input type="checkbox" class="form-check-input btn-radio-voices" onclick="setInputVoicePosition($(this), true)">';
    tempRow += '      <input type="hidden" name="radio_voices[]" value="">';
    tempRow += '    </div>';
    tempRow += '  </td>';
    tempRow += '  <td>';
    tempRow += '    <input class="form-control disabled"  type="number" name="voice_positions[]" min="1" max="15" value="" readonly>';
    tempRow += '  </td>';
    tempRow += '  <td><button type="button" class="btn btn-danger btn-remove-column" onclick="removeColumn($(this))">X</button></td>';
    tempRow += '</tr>';

    $('#table-header-list-container tbody').append(tempRow);
    headerCount++;
    checkHeaderCount()
  };

  function removeColumn(e) {
    e.parent().parent().remove();
    --headerCount;
    checkHeaderCount();

    var inputVoicePosition = $(e).parent().prev().children().first();
    checkVoiceSequence(inputVoicePosition, false);
  };

  function checkHeaderCount() {
    if (headerCount <= 0) {
      $('#btn-submit-template').removeClass('btn-primary').addClass('btn-outline-primary disabled');
      $('#row-no-template-data').show();
      $('#row-template-data').hide();
    }
    else {
      $('#btn-submit-template').removeClass('btn-outline-primary disabled').addClass('btn-primary');
      $('#row-no-template-data').hide();
      $('#row-template-data').show();
    }
  };

  function setInputVoicePosition(e, isForVoicePos) {
    var inputVoicePosition = $(e).parent().parent().next().children().first();

    if ($(e).is(':checked')) {
      $(e).next().val('on');
      if (isForVoicePos) {
        checkVoiceSequence(inputVoicePosition, true);
      }
    }
    else {
      $(e).next().val('');
      if (isForVoicePos) {
        checkVoiceSequence(inputVoicePosition, false);
      }
    }
  };

  function checkVoiceSequence(e, isAddition) {
    if (isAddition) {
      $(e).val(voiceSequenceList.length + 1);
      voiceSequenceList.push({
        el: e, seq: voiceSequenceList.length + 1
      });
    }
    else {
      var tempList = [];
      var seqValue = $(e).val();
      
      voiceSequenceList.map((v, k) => {
        if (v.seq != seqValue) {
          tempList.push(v);
        }
        else {
          $(v.el).val('');
        }
      });

      tempList.map((v, k) => {
        v.seq = k + 1;
        $(v.el).val(v.seq);
      });

      voiceSequenceList = tempList;
    }
  };

  $(document).ready(function() {
    $("#table-header-list-container").sortable({
      items: 'tr'
    }).disableSelection();

    $('#btn-add-column').click(function(e) {
      addColumn();
    });

    $('.btn-remove-column').click(function(e) {
      removeColumn($(this));
    });

    $('.btn-radio-voices').on('check', function(e) {
      setInputVoicePosition($(this), true);
    });

    $('.btn-radio-mandatories, .btn-radio-uniques').on('check', function(e) {
      setInputVoicePosition($(this), false);
    });

    $('.spinner-progress').hide();
    $('.btn-back').click(function(e) {
      window.history.back();
    });

    checkHeaderCount();
  });
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')