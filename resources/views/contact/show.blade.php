@include('layouts.include_page_header')
@include('layouts.include_sidebar')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Contacts</h1>
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
                Detail Contact
            </h5>

            <div class="row">
              <div class="col">
                <div class="row">
                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">No.</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-4 col-md-8 h5" id="dialog-detail-contact-sequence">{{ $contact->id }}</div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Account ID</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-8 col-md-8 h5" id="dialog-detail-contact-account-id">{{ $contact->account_id }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Name</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-8 col-md-8 h5" id="dialog-detail-contact-name">{{ $contact->name }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Phone</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-8 col-md-8 h5" id="dialog-detail-contact-phone">{{ $contact->phone }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col">
                <div class="row">
                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Bill Date</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-8 col-md-8 h5" id="dialog-detail-contact-bill-date">{{ $contact->bill_date }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Due Date</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-8 col-md-8 h5" id="dialog-detail-contact-due-date">{{ $contact->due_date }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-5 col-md-4 label">Nominal (Rp.)</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-7 col-md-8 h5" id="dialog-detail-contact-nominal">{{ $contact->nominal }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col">
                <div class="row">
                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-lg-5 col-md-4 label">Call Dial</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-7 col-md-8 h5" id="dialog-detail-contact-call-dial">{{ $contact->call_dial or '-' }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-5 col-md-4 label">Call Connect</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-7 col-md-8 h5" id="dialog-detail-contact-call-connect">{{ $contact->call_connect or '-' }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-6 col-md-4 label">Call Disconnect</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-8 col-md-8 h5" id="dialog-detail-contact-call-disconnect">{{ $contact->call_disconnect or '-' }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-5 col-md-4 label">Call Duration</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-7 col-md-8 h5" id="dialog-detail-contact-call-duration">{{ $contact->call_duration or '0' }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-5 col-md-4 label">Call Response</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-7 col-md-8 h5" id="dialog-detail-contact-call-response">{{ $contact->call_response or '-' }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-5 col-md-4 label">Total Calls</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-7 col-md-8 h5" id="dialog-detail-contact-total-calls">{{ $contact->total_calls or '0' }}</div>
                    </div>
                  </div>
                </div>

              </div>
            </div>

            <div class="row mt-4">
              <button type="button" class="btn btn-secondary btn-lg btn-block btn-back" data-bs-dismiss="modal">Close</button>
            </div>

          </div>
        </div>

      </div>
    </div>
  </section>
</main>

@push('javascript')
<script type="text/javascript">
  $(document).ready(function(e) {
    $('.btn-back').click(function(e) {
      history.back();
    });
  });
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
