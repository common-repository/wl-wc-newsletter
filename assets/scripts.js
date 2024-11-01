jQuery(function($) {
    alertify.defaults.glossary.title= "WL Newsletter for WooCommerce";
    /*
    Displaying alertify success & error messages;
    alertify.error().delay(0).setContent('error');
    alertify.success().delay(0).setContent('success');
    */

    select2_wlwcn_config = {
            placeholder:"Select items",
            allowClear: true
        };

    select2_wlwcn_single_config = {
            placeholder:"Select item",
            allowClear: true
        };

    $(function() {
        $timezone = $('body').find('input[name=timezone]');
        if($timezone.length)
        {
            dateObj = new Date();
            $timezone.val(dateObj.getTimezoneOffset());
        }

        $select2 = $('.select2-wlwcn');
        if($select2.length)
        {
            $('.select2-wlwcn').select2(select2_wlwcn_config);
        }

        $select2_single = $('.select2-wlwcn-edit-mailing-list');
        if($select2_single.length)
        {
            $('.select2-wlwcn-edit-mailing-list').select2(select2_wlwcn_config);
        }
    });

    $('body').on('focus', 'input[name=datetime]', function(e){
        $this = $(this);
        mindate = new Date(moment().add(5, 'minutes'));
        $this.datetimepicker({
            minDate: mindate,
            dateFormat: "yy-mm-dd"
        });
    });

    $('body').on('change', 'input[name=when_to_send]', function(e) {
		$this = $(this);
		val = $this.val();
        $fg = $this.closest('.form-group');

        $child = $fg.find('.child-1');

        if($child.length)
        {
    		if(val == 'later')
    		{
    			$child.removeClass('d-none');
    		}
    		else
    		{
    			$child.addClass('d-none');
    		}
        }
	});

    $('body').on('change', 'input.child-type', function(e) {
		$this = $(this);
		val = $this.val();
		select_vals = ['selected', 'except'];

		$select2 = $this.closest('.form-group').find('select');
		$fs = $select2.closest('fieldset');
		if(select_vals.includes(val))
		{
			$fs.removeClass('d-none');
			$select2.select2(select2_wlwcn_config);
			$select2.prop('required', true);
		}
		else
		{
			$select2.prop('required', false);
			$fs.addClass('d-none');
		}
	});

    $('body').on('change', 'input.child-type-edit-mailing-list', function(e) {
        $this = $(this);
        val = $this.val();
		select_vals = ['selected', 'except'];

		$select2 = $this.closest('.form-group').find('select');
		$fs = $select2.closest('fieldset');
		if(select_vals.includes(val))
		{
			$fs.removeClass('d-none');
			$select2.select2(select2_wlwcn_config);
			select2_required = (val == 'except');
		}
		else
		{
            select2_required = false;
			$fs.addClass('d-none');
		}

        $select2.prop('required', select2_required);
    });

    $('body').on('change', '.primary-selector > input[type=checkbox]', function(e) {
        $this = $(this);
        $parent = $this.closest('.form-group');
        $select2 = $parent.find('select');
        $radios = $parent.find('input[type=radio]')

        if($this.is(':checked'))
		{
			$parent.find('.child-1').removeClass('d-none');

			if($radios.length)
			{
				$radios.prop('required', true);

				radio_val = false;
				$radios.each(function() {
					_this = $(this);
					if(_this.is(':checked'))
					{
						radio_val = _this.val();
					}
				});

				if(radio_val)
				{
					select_vals = ['1', '2'];
					if(select_vals.includes(radio_val))
					{
						$select2.closest('fieldset').removeClass('d-none');
						$select2.select2(select2_wlwcn_config).prop('required', true);
					}
					else
					{
						$select2.closest('fieldset').addClass('d-none');
						$select2.prop('required', false);
					}
				}
			}
		}
		else
		{
			$parent.find('.child-1, .child-2').addClass('d-none');
			if($radios.length)
			{
				$radios.prop('required', false);
                $radios.prop('checked', false);
			}
			$select2.prop('required', false);
		}
    });

    $('body').on('change', 'input[name=enable_subscription_offer]', function(e) {
        $this = $(this);
        $form = $this.closest('form');
        if($this.is(":checked"))
        {
            $form.find('input[name=enable_subscription]').prop('checked', true);
        }
    });

    $('body').on('change', 'input[name=enable_subscription]', function(e) {
        $this = $(this);
        $form = $this.closest('form');
        if(!$this.is(":checked"))
        {
            $form.find('input[name=enable_subscription_offer]').prop('checked', false);
        }
    });

    $('body').on('change', 'select[name=discount_type]', function(e) {
        $this = $(this);
        $form = $this.closest('form');

        val = $this.val();
        $amt = $form.find('input[name=discount_amount]');

        if(val == 'percent')
        {
            max = 100;
            $amt.closest('div').find('span.symbol.pre').addClass('d-none');
            $amt.closest('div').find('span.symbol.post').removeClass('d-none');
        }
        else
        {
            max = '';
            $amt.closest('div').find('span.symbol.post').addClass('d-none');
            $amt.closest('div').find('span.symbol.pre').removeClass('d-none');
        }
        $amt.attr('max', max);
    });

    $('body').on('submit', '.form-delete', function(e) {
        $this = $(this);
        confirmed = $this.data('confirmed');

		if(!confirmed)
		{
			e.preventDefault();
			alertify.confirm("Are you sure you want to delete this ?",
				// On OK
				function(al) {
					$this.data('confirmed', 1);
                    $this.submit();
				},

				// On cancel
				function(al) {

				}
			).set('defaultFocus', 'cancel');
		}
    });

    $('body').on('submit', '.form-newsletter', function(e) {
        $this = $(this);
        confirmed = $this.data('confirmed');
        when = $this.find('input[name=when_to_send]:checked').val();

		if(!confirmed && (when == 'now'))
		{
            e.preventDefault();
            alertify.confirm("Are you sure you want to send this newsletter now ?",
                // On OK
                function(al) {
                    $this.data('confirmed', 1);
                    $this.submit();
                },

                // On cancel
                function(al) {

                }
            ).set('defaultFocus', 'cancel');
		}
    });

    function removeDataColName()
    {
        $('body').find('.wrap.wlnl.custom table td').attr('data-colname', '');
    }

    win_w = $(window).width();
    if(win_w < 783)
    {
        removeDataColName();
    }

    $(window).resize(function() {
        win_w = $(window).width();
        if(win_w < 783)
        {
            removeDataColName();
        }
    });

    $('body').on('change', 'input[name=send_to]', function(e) {
        $this = $(this);
        val = $this.val();

        $fg = $this.closest('.form-group');
        $form = $fg.closest('form');
        $now = $form.find('input[name=when_to_send][value=now]');

        mailing_list_vals = ['selected_mailing_list'];

        if(val == 'none')
        {
            $form.find('input[name=when_to_send][value=later]').prop('checked', true).trigger('change');
            $form.find('.wlwcn-dt-container').addClass('d-none');

            $now.prop('disabled', true);
        }
        else
        {
            $fieldset = '';
            if(val == 'selected_mailing_list')
            {
                $fieldset = $fg.find('.child-1.select2-wlwcn-common.wlwcn-mailing-lists');
            }
            else if(val == 'selected_subscriber')
            {
                $fieldset = $fg.find('.child-1.select2-wlwcn-common.wlwcn-subscribers');
            }

            if($fieldset)
            {
                $fieldset.removeClass('d-none');
                $fieldset.find('select').prop('required', true).select2(select2_wlwcn_single_config);
            }
            else
            {
                $fg.find('.child-1.select2-wlwcn-common').addClass('d-none');
                $fg.find('.child-1.select2-wlwcn-common select').prop('required', false);
            }

            $when = $form.find('input[name=when_to_send][value=later]:checked');
            if($when.length)
            {
                $form.find('.wlwcn-dt-container').removeClass('d-none');
            }
            $now.prop('disabled', false);
        }
    });

});
