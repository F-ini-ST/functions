jQuery(function () {
    jQuery('#datepicker1').datepicker("option", "dateFormat", "yy-mm-dd");
    jQuery("#datepicker1").datepicker("option", "prevText", "<");
    jQuery("#datepicker1").datepicker("option", "nextText", ">");
    jQuery("#datepicker1").datepicker("option", "minDate", 0);
    jQuery(".fancybox").fancybox({
    });

});


function calc_form()
{
    var children_ages = $('.children_age').val() || 0;
    $('.children').attr('data',children_ages);
    var all_summ = 0;
    var currency = $('.total_summ').attr('data');
    var aduts = $('.adults').val();
    var aduts_price = $('.adults').attr('data');
    var children = $('.children').val();
    var children_price = $('.children').attr('data');

    all_summ = aduts_price * aduts + children * children_price;
    $('.total_summ').html(currency + ' ' + all_summ);
}


function book_tour()
{
    var data = $('.booking_form').serialize();
    $.ajax({
        method: 'get',
        url: '/wp-admin/admin-ajax.php?' + data,
        data: {
            action: 'booking_form',
        }, dataType: 'json'
    });
}


function select_city(cat_id) {
    if (cat_id=="") {
        var sel="<select name='rayon' id='rayon_select'><option value>Район </option></select>"
        $('.rayon_select_block').html(sel);
        initCustomForms();
    } else
    $.ajax({
        method: 'post',
        url: '/wp-admin/admin-ajax.php',
        data: {
            action: 'select_city',
            cat_id: cat_id
        },
        //dataType: 'json',
        success: function (data) {
            $('.rayon_select_block').html(data);
            initCustomForms();
        }
    });
}

$(window).load(function () {

    $('.sort-form-frame').show();
});

(function ($) {

    $(document).ready(function () {

        $('.btns-wrap-2,.btns-wrap-1').find('a').click(function (e) {

            e.preventDefault();

            var href = $(this).attr("href"),
                    offsetTop = href === "#" ? 0 : $(href).offset().top - 100 + 1;
            $('html, body').stop().animate({
                scrollTop: offsetTop
            }, 300);

        });

		if($('.boxes .box').length){
			function rebuildBox(){
				var w = $(window).width(),
					rowCount = w>740?3:(w>400?2:1);
				$('.boxes .box div.item-content').height('auto');
				if(rowCount==1)return false;
				$('.boxes .box:nth-child('+rowCount+'n+1)').each(function(index, element){
					var max = 0, e, h;
					for(i = 0; i<rowCount; i++){
						max = (e = $('.boxes .box').eq(index*rowCount+i))&&(h = e.find('div.item-content').height())>max?h:max;
					}
					for(i = 0; i<rowCount; i++){
						if(e = $('.boxes .box').eq(index*rowCount+i))e.find('div.item-content').height(max)
					}
				});
			}

			$(window).on('resize', function(){
				rebuildBox();
			});
			rebuildBox();
		}
        

    $('.submit-holder a.submit').click(function(){
        var err=0;
        if ($('input[name=uname]').val() === '') {
            //$('input[name=uname]').attr('placeholder','Введите имя');
            err=1;
        }
        if ($('input[name=phone]').val() === '') {
            //$('input[name=phone]').attr('placeholder','Введите номер телефона');
            err=2;
        }
        if ($('input[name=email]').val() === '') {
            //$('input[name=email]').attr('placeholder','Введите email');
            err=3;
        }
        if ($('input[name=message]').val() === '') {
            //$('input[name=message]').attr('placeholder','Введите текст сообщения');
            err=4;
        }
        if (contact_us_verify(err)) {
            $('div#contact_result>p#contact_result_succ').show();
            $('div#contact_result>p#contact_result_fail').hide();
        } else {
            $('div#contact_result>p#contact_result_succ').hide();
            $('div#contact_result>p#contact_result_fail').show();
        }
        return false;
    });
    
    function contact_us_verify(err) {
        if (err===0) {
            sendMail();
            $('form.contact-form')[0].reset();
            return true;
        }
        else return false;
    }
    
    function sendMail() {
        var data = $('.contact-form').serialize();
        $.ajax({
            method: 'get',
            url: '/wp-admin/admin-ajax.php?' + data,
            data: {
                action: 'contact_form',
            }, dataType: 'json',
        });
    }

//    var start_time = document.getElementById('timepicker').options;
    var now = new Date();
    
    var hours = now.getHours();
    var minutes = now.getMinutes();
    if (hours<10) hours = '0'+hours;
    if (minutes<10) minutes = '0'+minutes;
    var tnow = hours+':'+minutes;
    
    var month = 1+now.getMonth();
    var day = now.getDate();
    if (month<10) month = '0'+month;
    if (day<10) day = '0'+day;
    var dnow = now.getFullYear()+'-'+month+'-'+day;
        
    // < skip today's date if no tours left for today (start time, i mean)
    var last_start_time = start_times[start_times.length-1];
    if (tnow > last_start_time) {
        $("#datepicker1").datepicker("option", "minDate", 1);
    }
    // >
    
    tour_start_time__fill(dnow);
    function tour_start_time__fill(date){
        var empty_option = '<option>-</option>';
        $('#timepicker').html(empty_option);
        $('#timepicker').change();
        
        for (var i=0; i<start_times.length; i++){
            if (date!=dnow || start_times[i]>tnow){
                $('#timepicker').append('<option>'+start_times[i]+'</option>');
            }
        }
    }

    $("#datepicker1").change(function(){
        tour_start_time__fill($(this).val());        
//        var time_p = $("#timepicker")[0];
//        if ($(this).val()==dnow) {
//            time_p.selectedIndex = -1;
//            for (var i=0;i<start_time.length;i++){
//                if (start_time[i].value<tnow)
//                    $(start_time[i]).attr('disabled','disabled');
//                else
//                    if(time_p.selectedIndex==-1) {
//                        time_p.selectedIndex = i;
//                        $(time_p).change();
//                    }
//            }
//        } else {
//            for (var i=0;i<start_time.length;i++){
//                $(start_time[i]).removeAttr('disabled');
//            }
//        }
    });
    
    });


})(jQuery);
