@include('layouts.include_page_header')
@include('layouts.include_sidebar')

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
              <a href="{{ route('templates.create') }}" id="btn-add-template" class="btn btn-primary btn-user-action">
                Add Template
              </a>
            </h5>
            
            <table id="table-template-list-container" class="table table-hover">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Name</th>
                  <th scope="col">Action</th>
                </tr>
              </thead>

              <tbody>
                @if(count($templates) > 0)
                  @foreach($templates AS $keyTemplate => $valueTemplate)
                    <tr>
                      <td class="text-end">{{ $loop->index + 1 }}.</td>
                      <td>{{ $valueTemplate->name }}</td>
                      <td>
                        @if ($loop->index > 0)
                        {{-- <a href="{{ route('campaigns.edit', ['id' => $valueTemplate->id]) }}" class="btn btn-sm btn-warning btn-modal-spinner">Edit</a> --}}
                        <a href="{{ route('campaigns.delete', ['id' => $valueTemplate->id]) }}" class="btn btn-sm btn-danger btn-modal-spinner">Delete</a>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                @else
                  <tr>
                    <td colspan="3" class="text-center">There's no templates data</td>
                  </tr>
                @endif
              </tbody>
            </table>

            {{-- {{ $templates->links() }} --}}

          </div>
        </div>

      </div>
    </div>
  </section>
</main>

@push('javascript')
<script type="text/javascript">
  $(document).ready(function() {
  });
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')