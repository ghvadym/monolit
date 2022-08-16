document.addEventListener('DOMContentLoaded', function () {
    var ajax = myajax.ajaxurl;
    var searchIcon = document.querySelector('.search_icon');
    var searchWrap = document.querySelector('.search_flex');
    var searchResultWrap = document.querySelector('.search_result_flex');
    var searchResult = document.querySelector('.search_result');
    var formInput = document.querySelector('.search-form input');
    var triangle = document.querySelector('.triangle');

    if (searchIcon) {
        searchIcon.addEventListener('click', function () {
            var svg = searchIcon.querySelectorAll('svg');

            svg.forEach((item) => {
                item.classList.toggle('active');
            });

            triangle.classList.toggle('active');
            searchWrap.classList.toggle('active');
            searchResultWrap.classList.toggle('active');

            detectSearchInput(formInput, searchWrap);
        });
    }

    function detectSearchInput(formInput, searchWrap)
    {
        if (!formInput && !searchWrap.classList.contains('active')) {
            return;
        }

        document.body.addEventListener('keypress', function (e) {
            setTimeout(function () {
                searchForm();
            }, 1000);
        });
    }

    async function searchForm()
    {
        var data = new FormData();
        var formInput = document.querySelector('.search-form input');
        data.append('action', 'search_form');
        data.append('input', formInput.value);

        var response = await fetch(ajax, {
            method: 'POST',
            body  : data
        });

        var html = await response.json();
        if (!html) {
            return;
        }

        searchResult.innerHTML = html.result;
    }
});