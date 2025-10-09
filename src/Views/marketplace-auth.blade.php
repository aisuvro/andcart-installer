@extends('installer::layouts.master')

@section('template_title')
    {{ trans('installer_messages.marketplace.templateTitle') }}
@endsection

@section('title')
    <i class="fa fa-key fa-fw" aria-hidden="true"></i>
    {!! trans('installer_messages.marketplace.title') !!}
@endsection

@section('container')
    <div class="tabs tabs-full">

        <input id="tab4" type="radio" name="tabs" class="tab-input" checked />
        <label for="tab4" class="tab-label">
            <i class="fa fa-key fa-2x fa-fw" aria-hidden="true"></i>
            <br />
            {{ trans('installer_messages.marketplace.tab') }}
        </label>

        <div class="tab tab-full">
            <div class="form-group {{ $errors->has('marketplace_username') ? ' has-error ' : '' }}">
                <label for="marketplace_username">
                    {{ trans('installer_messages.marketplace.form.marketplace_username_label') }}
                </label>
                <input type="text" name="marketplace_username" id="marketplace_username" value="{{ old('marketplace_username') }}" placeholder="{{ trans('installer_messages.marketplace.form.marketplace_username_placeholder') }}" />
                @if ($errors->has('marketplace_username'))
                    <span class="error-block">
                        <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                        {{ $errors->first('marketplace_username') }}
                    </span>
                @endif
            </div>

            <div class="form-group {{ $errors->has('marketplace_purchase_key') ? ' has-error ' : '' }}">
                <label for="marketplace_purchase_key">
                    {{ trans('installer_messages.marketplace.form.marketplace_purchase_key_label') }}
                </label>
                <input type="text" name="marketplace_purchase_key" id="marketplace_purchase_key" value="{{ old('marketplace_purchase_key') }}" placeholder="{{ trans('installer_messages.marketplace.form.marketplace_purchase_key_placeholder') }}" />
                @if ($errors->has('marketplace_purchase_key'))
                    <span class="error-block">
                        <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                        {{ $errors->first('marketplace_purchase_key') }}
                    </span>
                @endif
            </div>

            <div class="buttons">
                <button class="button" onclick="checkMarketplaceAuth();">
                    {{ trans('installer_messages.marketplace.form.buttons.verify') }}
                    <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script type="text/javascript">
        function checkMarketplaceAuth() {
            var username = document.getElementById('marketplace_username').value;
            var purchaseKey = document.getElementById('marketplace_purchase_key').value;
            
            if(username == '' || purchaseKey == '') {
                alert('{{ trans('installer_messages.marketplace.form.buttons.verify_error') }}');
                return false;
            }

            var button = document.querySelector('.button');
            button.classList.add('loading');
            button.innerHTML = '<i class="fa fa-spinner fa-spin fa-fw"></i>{{ trans('installer_messages.marketplace.form.buttons.verifying') }}';
            
            fetch('{{ route('LaravelInstaller::marketplace.verify') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    marketplace_username: username,
                    marketplace_purchase_key: purchaseKey
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    window.location.href = '{{ route('LaravelInstaller::dependencies') }}';
                } else {
                    alert(data.message);
                    button.classList.remove('loading');
                    button.innerHTML = '{{ trans('installer_messages.marketplace.form.buttons.verify') }} <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ trans('installer_messages.marketplace.form.buttons.network_error') }}');
                button.classList.remove('loading');
                button.innerHTML = '{{ trans('installer_messages.marketplace.form.buttons.verify') }} <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>';
            });
        }
    </script>
@endsection