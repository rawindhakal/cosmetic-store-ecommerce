<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('qr_payment_enabled', 'Status', 'Enable QR payment (eSewa / Khalti / FonePay / any QR)', $errors, $settings) }}
        {{ Form::text('translatable[qr_payment_label]', 'Label', $errors, $settings, ['required' => true]) }}
        {{ Form::textarea('translatable[qr_payment_description]', 'Description', $errors, $settings, ['rows' => 2, 'required' => true]) }}
        {{ Form::textarea('translatable[qr_payment_instructions]', 'Instructions shown to the customer', $errors, $settings, ['rows' => 4]) }}

        <p class="help-block">The order stays <b>Pending Payment</b> until you check your account and change the status in Sales &rarr; Orders.</p>
    </div>
</div>

@include('media::admin.image_picker.single', [
    'title' => 'QR code image',
    'inputName' => 'qr_payment_image',
    'file' => $qrImage,
])
