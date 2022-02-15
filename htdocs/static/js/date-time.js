var DateTime = {
    get: (date) => {
        var dateMoment = moment(date);        
        var data = {
            year: Number(dateMoment.format('YYYY')),
            month: Number(dateMoment.format('MM')),
            day: Number(dateMoment.format('DD'))
        }
        if (date.split(' ').length == 2) {
            data.isTime = true;
            data.hour = Number(dateMoment.format('HH'));
            data.minutes = Number(dateMoment.format('mm'));
            data.seconds = Number(dateMoment.format('ss'));
        }
        return data;
    },
    formatCVKey: (dateString) => {
        return dateString.replace(' ', '-').replace(':', '-');
    }
}
