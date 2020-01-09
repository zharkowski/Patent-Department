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

/**
 * Универсальный пикер
 * Поддерживает произвульную структуру элементов, поиск
 */
(function(context) {
    function ListPicker(configuration) {
        this.initialize(configuration);
    }

    ListPicker.prototype.initialize = function(configuration) {
        this.createElements();
        this.addEventListeners();
        configuration.wrapper.appendChild(this.elements.wrapper);

        this.processors = {
            liProcessor: configuration.liProcessor,         // как из объекта сделать html-элемент
            searchProcessor: configuration.searchProcessor  // должен ли объект obj выдаваться при поиске query
        };
        this.onItemClickedCallback = configuration.onItemClickedCallback; // будет вызвана при нажатии на объект, в аргументе будет передан сам объект
    };

    ListPicker.prototype.createElements = function() {
        this.elements = {};
        this.elements.searchInput = document.createElement('input');
        this.elements.searchInput.classList.add('list-picker__search-input');
        this.elements.searchInput.placeholder = 'Поиск';

        this.elements.list = document.createElement('ul');
        this.elements.list.classList.add('list-picker__list');

        this.elements.wrapper = document.createElement('div');
        this.elements.wrapper.classList.add('list-picker');
        this.elements.wrapper.appendChild(this.elements.searchInput);
        this.elements.wrapper.appendChild(this.elements.list);
    };

    ListPicker.prototype.addEventListeners = function() {
        // при измении инпута производим поиск
        this.elements.searchInput.addEventListener('input', function(e) {
            var query = this.elements.searchInput.value;
            var idsToShow = [];
            for (let i = 0; i < this.data.length; i++) {
                if (this.processors.searchProcessor(this.data[i], query) === true) {
                    idsToShow.push(i);
                }
            }

            this.renderData(idsToShow);
        }.bind(this));

        // при клике на область списка ищем элемент и сообщаем в колбэке что элемент найден
        this.elements.list.addEventListener('click', function(e) {
            var li = getParentWithClass(e.target, 'list-picker__li') || (e.target.classList.contains('list-picker__li') && e.target);
            if (li) {
                this.onItemClickedCallback(this.data[Number(li.getAttribute('data-id'))]);
            }
        }.bind(this));
    };

    ListPicker.prototype.populateWithData = function(dataArray) {
        this.data = dataArray;
        this.renderData(new Array(this.data.length).fill(0).map(function(_, i) {return i;}));
    };

    ListPicker.prototype.renderData = function(idsToShow) {
        this.elements.list.innerHTML = '';
        for (var i = 0; i < idsToShow.length; i++) {
            var li = document.createElement('li');
            li.classList.add('list-picker__li');
            li.setAttribute('data-id', idsToShow[i]);
            li.innerHTML = this.processors.liProcessor(this.data[idsToShow[i]]);
            this.elements.list.appendChild(li);
        }
    };

    ListPicker.prototype.show = function() {
        this.elements.wrapper.style.display = 'block';
    };

    ListPicker.prototype.hide = function() {
        this.elements.wrapper.style.display = 'none';
    };

    context.ListPicker = ListPicker;
})(window);

(function() {       //продление сессии при активности
    if (!document.cookie || document.cookie.indexOf('session_id') === -1) {
        return;
    }

    var sessionExpireTimeout = undefined;
    var userActionFrequency = 15*1000;
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
        window.location = '/index.php';
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