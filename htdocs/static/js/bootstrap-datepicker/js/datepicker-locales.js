/**
 * Set default settings for bootstrap-datepicker
 */
$(document).ready(function(){
    $.fn.datepicker.dates[Message.locale.language] = {
        days: Message.locale.weekDayNamesSA.wide,
        daysShort: Message.locale.weekDayNamesSA.abbreviated,
        daysMin: Message.locale.weekDayNamesSA.abbreviated,
        months: Message.locale.monthNamesSA.wide,
        monthsShort: Message.locale.monthNamesSA.abbreviated,
		today: Message.locale.dateFormats.today,
		weekStart: 1,
        format: Message.locale.dateFormats.medium,
        
    };
});



