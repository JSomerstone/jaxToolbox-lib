/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
X = function()
{
    console.log(arguments);
}
jsHax =
{
    version : '0.1.0'
    , settings : {}
    , set : function (variable, value)
    {
        this.settings[variable] = value;
    }
    , get : function (variable)
    {
        return this.settings[variable];
    }
};

jsHax.generator =
{
    hetu : function(dateOfBirth, personNumber)
    {
        jsHax.assert.date(dateOfBirth);
        jsHax.assert.number(personNumber);

        var dash,
            sum,
            controlChar,
            controlNumber = '0123456789ABCDEFHJKLMNPRSTUVWXY',
            month = ("0" + (dateOfBirth.getMonth() + 1).toString()).slice(-2),
            day = ("0" + dateOfBirth.getDate()).slice(-2),
            year = ("0" + dateOfBirth.getYear()).slice(-2),
            century = dateOfBirth.getFullYear().toString().substr(0, 2),
            personNumberString = ("00" + personNumber).slice(-3)
        ;

        switch (century)
        {
            default:
            case '19':
                dash = '-';
                break;
            case '20':
                dash = 'A'
                break;
        }

        if(personNumber < 2 || personNumber > 899)
        {
            throw 'Invalid person number, must be between 2-899';
        }

        sum = parseInt(day + month + year + personNumberString, 10);

        controlChar = controlNumber.charAt(sum % 31);

        return day + month + year + dash + personNumberString + controlChar;
    }
}

jsHax.assert =
{
    date : function(anyGivenDate)
    {
        if ( !(anyGivenDate instanceof Date))
        {
            throw "Failed asserting that " + anyGivenDate +"' is a Date";
        }
    }

    , number : function (anyGivenNumber)
    {
        if ( typeof anyGivenNumber != "number")
        {
            throw "Failed asserting that " + anyGivenNumber +"' is a Number";
        }
    }
}

$(document).ready(function()
{
    $('#start').bind('click', function() {
        var dateZero = new Date(1950, 0, 1);

        for (var i=0 ; i < 5; i++)
        {
            for (var person = 2 ; person <= 10 ; person ++)
            {
                X(jsHax.generator.hetu(dateZero.addDays(i), person));

            }
        }
         /*$.ajax("https://palvelut.diacor.fi/", {
            type: 'GET',
            headers : {
                Cookie: 'SECUREWEBSTAGE11SESSION=2lqv70n1KUdAy2FEEvkrKiYCc3zBv_Lyh2R/i1HEwwPRrDpu'
            }
        })
        .success(function() { alert("second success"); })
        //.error(function() { alert("error"); })
        .error(function (XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.getAllResponseHeaders(), textStatus, errorThrown);
        })
        .complete(function(jqXHR) { console.log(jqXHR, jqXHR.getAllResponseHeaders()); });
        /*
        $.post({
            type: 'GET',
            url: 'https://palvelut.diacor.fi/',
            //data: data,
            success: function(data, textStatus, jqXHR)
            {
                X(data, textStatus, jqXHR);
            },
            dataType: 'html'
        });*/
        //$.post('https://palvelut.diacor.fi/', function(data) {
        //    $('#output').html(data);
        //});
    });
});

Date.prototype.addDays = function(days)
 {
     var dat = new Date(this.valueOf())
     dat.setDate(dat.getDate() + days);
     return dat;
 }