/**
 * Generic AJAX Request class
 */
(function(context) {
    function AjaxRequest(options) {
        this.initialize(options);
    }

    AjaxRequest.prototype.initialize = function(options) {
        this.httpMethod = options.method;
        this.url = options.url;
        this.requestBody = options.requestBody;
        this.onSuccessCallback = options.onSuccessCallback;
        this.onFailureCallback = options.onFailureCallback;
    };

    AjaxRequest.prototype.send = function() {
        var xhr = new XMLHttpRequest();
        xhr.open(this.httpMethod, this.url);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    this.onSuccessCallback(xhr.response);
                } else {
                    this.onFailureCallback();
                }
            }
        }.bind(this);

        this.requestBody ? xhr.send(this.requestBody) : xhr.send();
    };

    context.AjaxRequest = AjaxRequest;
})(window);

/**
 * AJAX API - используем AjaxRequest для создания функции-запроса к API
 */
(function(context) {
    function APIRequest(action, options, onSuccessCallback, onFailureCallback) {
        var post = JSON.stringify({}) !== "{}";
        var ajaxRequest = new AjaxRequest({
            method: post ? 'POST' : 'GET',
            url: '/handlers/ajax/?action=' + action,
            requestBody: post ? 'data=' + JSON.stringify(options): undefined,
            onSuccessCallback: function(response) {
                var responseJSON = undefined;
                try {
                    responseJSON = JSON.parse(response);
                } catch(e) {
                    onFailureCallback();
                }

                if (responseJSON && responseJSON.status === 'ok') {
                    onSuccessCallback(responseJSON.data);
                    return;
                }
                onFailureCallback(responseJSON ? responseJSON.error_msg : undefined);
            },
            onFailureCallback: onFailureCallback
        });
        ajaxRequest.send();
    }

    context.APIRequest = APIRequest;
})(window);

(function() {       //продление сессии при активности
    if (!document.cookie || document.cookie.indexOf('session_id') === -1) {
        return;
    }

    var sessionExpireTimeout = undefined;
    var userActionFrequency = 60*1000;
    var lastUserActionTimestamp = +new Date();
    var cookieExpireTime = 120*1000;
    var popupShown = false;

    function initSessionExpireTimeout() {
        if (sessionExpireTimeout) {
            clearTimeout(sessionExpireTimeout);
            sessionExpireTimeout = setTimeout(function () {
                alertAndRedirect();
            }, cookieExpireTime);
        }
    }
    function alertAndRedirect() {
        if (popupShown) {
            return;
        }
        popupShown = true;
        alert('Вы не проявляли активность 2 минуты. Вы будете перенаправлены в окно авторизации');
        window.location = '/';
    }
    function registerUserAction() {
        var currentTimestamp = +new Date();
        if (currentTimestamp - lastUserActionTimestamp < userActionFrequency) {
            return;
        }

        APIRequest('get_new_session', {}, function(response) {
            lastUserActionTimestamp = currentTimestamp;
            initSessionExpireTimeout();
        }, function(error_msg) {
            alertAndRedirect();
            initSessionExpireTimeout();
        });
    }

    window.addEventListener('click', registerUserAction);
    window.addEventListener('mousemove', registerUserAction);
})();