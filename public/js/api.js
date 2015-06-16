function purge_request(service, account, form) {
    return new Promise(function (resolve, reject) {
        var fd = new FormData(form);
        var url = '/api/purge/' + service + '/' + account + '.json';
        $.ajax(url, {
            method: 'POST',
            contentType: false,
            processData: false,
            data: fd,
            dataType: 'json',
            success: function (data, dataType) {
                console.log('purge request success');
                resolve(data);
            },
            error: function (req, msg, err) {
                console.log('purge request error: ');
                console.log(err);
                reject(err);
            }
        }
        );
    });
}

function list_queue(service, account) {
    return new Promise(function (resolve, reject) {
        var url = '/api/queue/' + service + '/' + account + '.json';
        $.ajax(url, {
            method: 'GET',
            contentType: false,
            processData: false,
            success: function (data, dataType) {
                console.log('list queue success');
                resolve(data);
            },
            error: function (req, msg, err) {
                console.log('list queue error: ');
                console.log(err);
                reject(err);
            }
        }
        );
    });
}
