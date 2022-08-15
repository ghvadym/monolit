const $ = jQuery;

document.addEventListener('DOMContentLoaded', function () {
// $(document).ready(function () {

    // SLIDER
    function carousel(element, name) {
        if (element.length > 0) {
            const slides = element[0].querySelector("[data-slides]")
            const btns = element[0].querySelectorAll("[data-carousel-button]")
            const activeSlide = slides.querySelectorAll(".slide")
            activeSlide[0].dataset.active = true;
            btns.forEach(button => {
                button.addEventListener("click", () => {
                    const offset = button.dataset.carouselButton === "next" ? 1 : -1
                    const slides = button
                        .closest(name)
                        .querySelector("[data-slides]")

                    const activeSlide = slides.querySelector("[data-active]")
                    let newIndex = [...slides.children].indexOf(activeSlide) + offset
                    if (newIndex < 0) newIndex = slides.children.length - 1
                    if (newIndex >= slides.children.length) newIndex = 0

                    slides.children[newIndex].dataset.active = true
                    delete activeSlide.dataset.active
                })
            })
        }
    }
    const carouselButtons = document.querySelectorAll("[data-carousel]")
    carousel(carouselButtons, "[data-carousel]")

    // SLIDER


    // GALLERY

    // SLIDER
    function carouselGallery(element) {
        if (element.length > 0) {
            const slides = element[0].querySelector("[data-slides]")
            const btns = element[0].querySelectorAll("[data-carousel-button]")
            const slidesArr = slides.querySelectorAll(".slideGallery")
            slidesArr[1].dataset.active = true;
            let data = []
            for (let i = 0; i < slidesArr.length; i++) {
                data[i] = {};
                // data[i].url = slidesArr[i].baseURI;
                data[i].title = slidesArr[i].children[0].children[1].innerText || ''
                data[i].src = slidesArr[i].children[0].children[1].dataset.href || ''
            }
            if (data.length >= 3) {
                const prevSlideA = slides.querySelector("[data-active]").querySelector(".gallery_slide_position").querySelectorAll("a")[0];
                const prevSlideIMG = prevSlideA.querySelector("img");
                const prevSlideP = prevSlideA.querySelector("p");

                const nextSlideA = slides.querySelector("[data-active]").querySelector(".gallery_slide_position").querySelectorAll("a")[2];
                const nextSlideIMG = nextSlideA.querySelector("img");
                const nextSlideP = nextSlideA.querySelector("p");

                prevSlideA.className = 'prev_slide';
                // prevSlideA.href = data[0].url;
                prevSlideIMG.src = data[0].src;
                prevSlideIMG.alt = data[0].title;
                prevSlideP.textContent = data[0].title;


                nextSlideA.className = 'next_slide';
                // nextSlideA.href = data[2].url;
                nextSlideIMG.src = data[2].src;
                nextSlideIMG.alt = data[2].title;
                nextSlideP.textContent = data[2].title;

                setInterval(() => buttonClick(btns[1]), 7000);

                function buttonClick(button) {
                    const offset = button.dataset.carouselButton === "next" ? 1 : -1
                    const activeSlide = slides.querySelector("[data-active]")
                    let newIndex = [...slides.children].indexOf(activeSlide) + offset
                    let newPrevIndex = newIndex - 1
                    let newNextIndex = newIndex + 1

                    if (newIndex < 0) {
                        newPrevIndex = slides.children.length - 2
                        newIndex = slides.children.length - 1
                    }

                    if (newIndex == 0) {
                        newNextIndex = 1
                        newPrevIndex = slides.children.length - 1
                    }

                    if (newIndex >= slides.children.length) {
                        newPrevIndex = slides.children.length - 1
                        newIndex = 0
                    }

                    if (newNextIndex >= slides.children.length) {
                        newNextIndex = 0
                        if (newIndex == 0) newNextIndex = 1

                    }

                    const prevSlideA = slidesArr[newIndex].querySelectorAll("a")[0]
                    const prevSlideIMG = prevSlideA.querySelector("img");
                    const prevSlideP = prevSlideA.querySelector("p");

                    const nextSlideA = slidesArr[newIndex].querySelectorAll("a")[2]
                    const nextSlideIMG = nextSlideA.querySelector("img");
                    const nextSlideP = nextSlideA.querySelector("p");

                    prevSlideA.className = 'prev_slide';
                    prevSlideIMG.src = data[newPrevIndex].src;
                    prevSlideIMG.alt = data[newPrevIndex].title;
                    prevSlideP.textContent = data[newPrevIndex].title;

                    nextSlideA.className = 'next_slide';
                    nextSlideIMG.src = data[newNextIndex].src;
                    nextSlideIMG.alt = data[newNextIndex].title;
                    nextSlideP.textContent = data[newNextIndex].title;

                    slides.children[newIndex].dataset.active = true
                    delete activeSlide.dataset.active
                }

                btns.forEach(button => {
                    button.addEventListener("click", () => buttonClick(button))
                })
            }
        }
    }

    const galleryButtons = document.querySelectorAll("[data-gallery]")
    carouselGallery(galleryButtons, "[data-gallery]")

    // GALLERY


    // PARTHERS

    function carouselPartners(element) {
        if (element.length > 0) {
            const slides = element[0].querySelector("[data-slides]")
            const btns = element[0].querySelectorAll("[data-partners-button]")
            const slidesArr = slides.querySelectorAll(".slidePartners")
            slidesArr[1].dataset.active = true;
            let data = []
            for (let i = 0; i < slidesArr.length; i++) {
                data[i] = {};
                data[i].src = slidesArr[i].children[0].children[1].children[0].children[0].src || ''
            }
            if (data.length >= 3) {
                const prevSlideDIV = slides.querySelector("[data-active]").querySelector(".partners_slide_position").querySelectorAll("div")[0];
                const prevSlideA = prevSlideDIV.querySelector("a");
                const prevSlideIMG = prevSlideA.querySelector("img");

                const nextSlideDIV = slides.querySelector("[data-active]").querySelector(".partners_slide_position").querySelectorAll("div")[2];
                const nextSlideA = nextSlideDIV.querySelector("a");
                const nextSlideIMG = nextSlideA.querySelector("img");

                prevSlideIMG.src = data[0].src;

                nextSlideIMG.src = data[2].src;

                if (window.screen.width <= 768) nextSlideDIV.style.display = 'none';

                if (window.screen.width <= 425) prevSlideDIV.style.display = 'none';

                setInterval(() => buttonClick(btns[1]), 7000);

                function buttonClick(button) {
                    const offset = button.dataset.partnersButton === "next" ? 1 : -1
                    const activeSlide = slides.querySelector("[data-active]")
                    let newIndex = [...slides.children].indexOf(activeSlide) + offset
                    let newPrevIndex = newIndex - 1
                    let newNextIndex = newIndex + 1

                    if (newIndex < 0) {
                        newPrevIndex = slides.children.length - 2
                        newIndex = slides.children.length - 1
                    }

                    if (newIndex == 0) {
                        newNextIndex = 1
                        newPrevIndex = slides.children.length - 1
                    }

                    if (newIndex >= slides.children.length) {
                        newPrevIndex = slides.children.length - 1
                        newIndex = 0
                    }

                    if (newNextIndex >= slides.children.length) {
                        newNextIndex = 0
                        if (newIndex == 0) newNextIndex = 1

                    }

                    const prevSlideDIV = slidesArr[newIndex].querySelectorAll("div")[1]
                    const prevSlideA = prevSlideDIV.querySelector("a");
                    const prevSlideIMG = prevSlideA.querySelector("img");

                    const nextSlideDIV = slidesArr[newIndex].querySelectorAll("div")[3]
                    const nextSlideA = nextSlideDIV.querySelector("a");
                    const nextSlideIMG = nextSlideA.querySelector("img");

                    prevSlideIMG.src = data[newPrevIndex].src;

                    if (window.screen.width <= 768) nextSlideDIV.style.display = 'none';

                    if (window.screen.width <= 425) prevSlideDIV.style.display = 'none';

                    nextSlideIMG.src = data[newNextIndex].src;

                    slides.children[newIndex].dataset.active = true
                    delete activeSlide.dataset.active
                }
                btns.forEach(button => {
                    button.addEventListener("click", () => buttonClick(button))
                })
            }
        }
    }

    const partnersButtons = document.querySelectorAll("[data-partners]")
    carouselPartners(partnersButtons)


    // PARTHERS

    // CERTIFICATES

    function carouselCertif(element) {
        if (element.length > 0) {
            const slides = element[0].querySelector("[data-slides]")
            const btns = element[0].querySelectorAll("[data-certif-button]")
            const slidesArr = slides.querySelectorAll(".slideCertif")
            slidesArr[1].dataset.active = true;
            let data = []
            for (let i = 0; i < slidesArr.length; i++) {
                data[i] = {};
                data[i].src = slidesArr[i].children[0].children[1].children[0].children[0].src || ''
            }
            if (data.length >= 3) {
                const prevSlideDIV = slides.querySelector("[data-active]").querySelector(".certif_slide_position").querySelectorAll("div")[0];
                const prevSlideA = prevSlideDIV.querySelector("a");
                const prevSlideIMG = prevSlideA.querySelector("img");

                const nextSlideDIV = slides.querySelector("[data-active]").querySelector(".certif_slide_position").querySelectorAll("div")[2];
                const nextSlideA = nextSlideDIV.querySelector("a");
                const nextSlideIMG = nextSlideA.querySelector("img");

                prevSlideIMG.src = data[0].src;

                nextSlideIMG.src = data[2].src;

                if (window.screen.width <= 768) nextSlideDIV.style.display = 'none';

                if (window.screen.width <= 425) prevSlideDIV.style.display = 'none';

                setInterval(() => buttonClick(btns[1]), 7000);

                function buttonClick(button) {
                    const offset = button.dataset.certifButton === "next" ? 1 : -1
                    const activeSlide = slides.querySelector("[data-active]")
                    let newIndex = [...slides.children].indexOf(activeSlide) + offset
                    let newPrevIndex = newIndex - 1
                    let newNextIndex = newIndex + 1

                    if (newIndex < 0) {
                        newPrevIndex = slides.children.length - 2
                        newIndex = slides.children.length - 1
                    }

                    if (newIndex == 0) {
                        newNextIndex = 1
                        newPrevIndex = slides.children.length - 1
                    }

                    if (newIndex >= slides.children.length) {
                        newPrevIndex = slides.children.length - 1
                        newIndex = 0
                    }

                    if (newNextIndex >= slides.children.length) {
                        newNextIndex = 0
                        if (newIndex == 0) newNextIndex = 1

                    }

                    const prevSlideDIV = slidesArr[newIndex].querySelectorAll("div")[1]
                    const prevSlideA = prevSlideDIV.querySelector("a");
                    const prevSlideIMG = prevSlideA.querySelector("img");

                    const nextSlideDIV = slidesArr[newIndex].querySelectorAll("div")[3]
                    const nextSlideA = nextSlideDIV.querySelector("a");
                    const nextSlideIMG = nextSlideA.querySelector("img");

                    prevSlideIMG.src = data[newPrevIndex].src;

                    if (window.screen.width <= 768) nextSlideDIV.style.display = 'none';

                    if (window.screen.width <= 425) prevSlideDIV.style.display = 'none';

                    nextSlideIMG.src = data[newNextIndex].src;

                    slides.children[newIndex].dataset.active = true
                    delete activeSlide.dataset.active
                }
                btns.forEach(button => {
                    button.addEventListener("click", () => buttonClick(button))
                })
            }
        }
    }

    // CERTIFICATES

    const certifButtons = document.querySelectorAll("[data-certif]")
    carouselCertif(certifButtons)


    let mobileBtn = document.querySelector('.mobile_bth');
    if (mobileBtn) {
        mobileBtn.addEventListener('click', function () {
            $(this).toggleClass('active');
            $('.primary-menu-container').toggleClass('active');
            $('.overflow').toggleClass('active');
            $('.menu-item').removeClass("active");
            $('.menu-item').removeClass("clicked");
            $('.menu-item').removeClass("show");
            $('body').toggleClass('no_scroll');
        });
    }

    // lang

    let langLabel = document.querySelector('.lang__label');
    if (langLabel) {
        langLabel.addEventListener('click', function () {
            $('.lang__label').removeClass('current-lang');
            $(this).addClass('current-lang');
        });
    }

    // Search

    function debounce(func, timeout = 1000) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }

    function emptyData(context) {
        let div = document.createElement("div");
        div.className = 'search-form_empty';
        div.innerHTML = 'Empty';
        context.appendChild(div);
    };

    function saveInput(value, context) {
        context.innerHTML = "";
        $.ajax({
            type: "GET",
            url: "/realtime_search",
            data: { query: value },
            success: (response = []) => {
                if (response) {
                    const data = JSON.parse(response);
                    if (data.length > 0) {
                        data.map((item = {}) => {
                            const { title = '', url = '#' } = item;
                            let a = document.createElement("a");
                            a.className = 'search-form_data';
                            a.innerHTML = `${title}`;
                            a.setAttribute("href", `${url}`);
                            context.appendChild(a);
                        })
                    } else {
                        emptyData(context)
                    }
                }
            },
            error: xhr => {
                console.error(xhr.status);
                emptyData(context);
            }
        });
    }

    const processChange = debounce((value, context) => saveInput(value, context), 500);

    document.querySelector('.search-form').addEventListener('input', event => {
        const search_result_flex = document.querySelector('.search_result_flex');
        const search_result = document.querySelectorAll('.search_result');
        if (event.target.value == '') {
            search_result_flex.classList.remove("active");
            search_result[0].innerHTML = "";
        } else {
            search_result_flex.classList.add("active");
            search_result[0].innerHTML = "";
            processChange(event.target.value, search_result[0])
        }
    });

    document.addEventListener('click', event => {
        const search_result_flex = document.querySelector('.search_result_flex');
        if (event.target.className == 'search-form_input') {
            search_result_flex.classList.add("active");
        } else {
            search_result_flex.classList.remove("active");
        }
    });

    let searchIcon = document.querySelector('.search_icon');
    if (searchIcon) {
        searchIcon.addEventListener('click', () => {
            document.querySelector('.search_icon_open').classList.toggle('active');
            document.querySelector('.search_icon_close').classList.toggle('active');
            document.querySelector('.search_flex').classList.toggle('active');
            document.querySelector('.triangle').classList.toggle('active');
        })
    }

    // END

    let overflow = document.querySelector('.overflow');
    if (overflow) {
        overflow.addEventListener('click', function () {
            console.log(123123121233)
            document.querySelector('.mobile_bth').classList.remove('active');
            document.querySelector('.primary-menu-container').classList.remove('active');
            document.querySelector('.overflow').classList.remove('active');
            document.querySelector('body').classList.remove('no_scroll');
            document.querySelector(".popap").classList.remove('active');
            document.querySelector(".popap_question").classList.remove('active');
            document.querySelector(".popap_thank_you").classList.remove("active");
            document.querySelector(".popap_thank_you.application").classList.remove("active");
        });
    }

    $.ajax({
        type: "GET",
        async: false,
        url: "/main_page_fq",
        success: function (data) {
            $(".asking").html(data);
        },
        error: function (xhr) {
            console.error(xhr.status);
        },
        dataType: "html",
    });


    $('.asking_item').on('click', function () {
        $(this).toggleClass('active');
        $(this).children('.ask_text_content').find('.answer').slideToggle();
    });

    $('.servise_description_button').on('click', function () {
        $(this).toggleClass('active');
        $(".servise_description_text").slideToggle();
    });

    $('.servise_description_text').on('click', function () {
        $(this).slideDown();
    });

    // Photogalery and Blog load more

    // Popap

    $('.order_servises').on('click', function (e) {
        e.preventDefault();
        let service_name = $(this).data('service');
        $("#popup_order_form_subtitle").text(service_name);
        $("#ordr_form_service").val(service_name);
        $(".popap").toggleClass("active");
        $('.overflow').addClass('active');
        $('body').toggleClass('no_scroll');
    });

    $('.popap_container').on('submit', function (event) {
        event.preventDefault();
        formData = $(this).serialize();
        // перевірка капчі
        grecaptcha.ready(function() {
            grecaptcha.execute('6LeoHX0eAAAAAD0u_u5jGKorDe67Lli0Vg6hNcvj', {action: 'submit'}).then(function(token) {
                // Add your logic to submit to your backend server here.
                const newFormData = formData + `&token=${token}`;
                $.ajax({
                    type: "POST",
                    url: "/contact_form_handler",
                    data: newFormData,
                    success: function (response) {
                        if (response) {
                            $(".popap").removeClass("active");
                            $(".popap_thank_you").addClass("active");
                            $('.popap_container').trigger('reset');
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.status);
                    }
                });
            });
        });
    })

    $('.close_arrow').on('click', function () {
        $(".popap").removeClass('active');
        $(".popap_question").removeClass('active');
        $('.overflow').removeClass('active');
        $('body').removeClass('no_scroll');
        $(".popap_thank_you").removeClass("active");
        $(".popap_thank_you.application").removeClass("active");
    });

    // Popap Question

    if ($('.order_question').length) {
        $('.order_question').on('click', function (e) {
            e.preventDefault();
            $(".popap_question").toggleClass("active");
            $('.overflow').addClass('active');
            $('body').toggleClass('no_scroll');
        });
    }

    $('.popap_container_question').on('submit', function (event) {
        event.preventDefault();
        console.log("popap_container_question_3");
        formData = $(this).serialize();
        // перевірка капчі
        grecaptcha.ready(function() {
            grecaptcha.execute('6LeoHX0eAAAAAD0u_u5jGKorDe67Lli0Vg6hNcvj', {action: 'submit'}).then(function(token) {
                // Add your logic to submit to your backend server here.
                const newFormData = formData + `&token=${token}`;
                $.ajax({
                    type: "POST",
                    url: "/contact_form_handler",
                    data: newFormData,
                    // dataType: "dataType",
                    success: function (response) {
                        if (response) {
                            // $(".popap_thank_you.application").addClass("active");
                            $(".popap_question").removeClass("active");
                            $(".popap_thank_you").addClass("active");
                            $('.popap_container_question').trigger('reset');
                            dataLayer.push({ 'event': 'Ask_question' });
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.status);
                    }
                });
            });
        });
    })

    // Popap Thank_you

    $('.contact_us').on('submit', function (event) {
        event.preventDefault();
        console.log('contact_us');
        formData = $(this).serialize();
        const formDataLayer = $('.contact_us').attr('id');
        console.log("formData", formData);
        // перевірка капчі
        grecaptcha.ready(function() {
            grecaptcha.execute('6LeoHX0eAAAAAD0u_u5jGKorDe67Lli0Vg6hNcvj', {action: 'submit'}).then(function(token) {
                // Add your logic to submit to your backend server here.
                const newFormData = formData + `&token=${token}`;
                $.ajax({
                    type: "POST",
                    url: $('.contact_us').attr('action'),
                    data: newFormData,
                    success: function (response) {
                        if (response) {
                            $(".popap_thank_you.application").addClass("active");
                            $('.overflow').addClass('active');
                            $('body').toggleClass('no_scroll');
                            $('.contact_us').trigger('reset');
                            $('.popap_container').trigger('reset');
                            $('.popap_container_question').trigger('reset')
                            formDataLayer && dataLayer.push({ 'event': formDataLayer });
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.status);
                    }
                });
            });
        });
    })

    // Photo_galery no_equal photo

    let photoContainer = document.getElementsByClassName('photo_galery_container');

    for (let i = 0; i < photoContainer.length; i++) {
        if (i % 3 == 1) {
            photoContainer[i].classList.add("not_equal");
        }//if statement

        if (i % 3 == 2) {
            photoContainer[i].classList.add("not_equal_reverse");
        }//if statement
    }

    // END

    // copilink

    function CopyLink() {
        copyTextToClipboard(location.href);
        alert('Cсылка успешно скопирована в буфер обмена.');
    }

    // end copilink

    $('.sub_menu_first_button').on('click', function () {
        $(this).parent().find('.sub-menu').toggleClass("show");
        $(this).toggleClass("active");

    });

    $('.sub-menu .menu-item').on('click', function () {

        $('.menu-item').removeClass("active");

        if (!$(this).hasClass("clicked")) {
            $('.menu-item').removeClass("clicked");
            $(this).addClass("active");
            $(this).addClass("clicked");
        } else {
            $(this).removeClass("active");
            $(this).removeClass("clicked");

        }
    });

    var element = $('.services_main_suptitle');
    var length = element.text().length;

    if (length < 258) {
        $('.servises_description').addClass("active");
    }
    //$("#id_phone, #id_pop_up_phone").mask("+380(99) 999-9999");
    // });
}, false);


$(window).on('load', function () {
    // Preloader
    let preloader = document.getElementById('preloader');
    if (preloader) {
        preloader.classList.add('hide-preloader');
        $('body.preloader_active').removeClass('preloader_active');
    }

    // Preloader_end
});

// Header Tel

function clearTelStr(className) {
    let telStr = document.querySelector(className);
    telStr && telStr.setAttribute('href', `tel:${telStr.textContent.replace(/[\s.,(,)]/g, '')}`);
}

clearTelStr('.header_tel')
clearTelStr('.footer_tel')
clearTelStr('.contacts_links')


// END

// Main Page

let serviceTitle = document.querySelectorAll('.services_item_title')
let maxHeight = 0;
serviceTitle.forEach(t => {
    if (t.scrollHeight > maxHeight) maxHeight = t.scrollHeight
})
serviceTitle.forEach(t => {
    t.style.height = +maxHeight + 'px'
    t.style.display = 'flex';
    t.style.justifyContent = 'center';
    t.style.alignItems = 'center';
})

// END