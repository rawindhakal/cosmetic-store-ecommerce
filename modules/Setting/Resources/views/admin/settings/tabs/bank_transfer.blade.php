<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('bank_transfer_enabled', 'Status', 'Enable direct bank transfer', $errors, $settings) }}
        {{ Form::text('translatable[bank_transfer_label]', 'Label', $errors, $settings, ['required' => true]) }}
        {{ Form::textarea('translatable[bank_transfer_description]', 'Description', $errors, $settings, ['rows' => 2, 'required' => true]) }}
        {{ Form::textarea('translatable[bank_transfer_instructions]', 'Bank account details', $errors, $settings, ['rows' => 5, 'required' => true]) }}

        <p class="help-block">Shown to customers at checkout and on the order page. The order stays <b>Pending Payment</b> until you verify the deposit and change its status.</p>
    </div>
</div>
