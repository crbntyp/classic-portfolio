var wWidth = $(window).width();

$(document).ready(function () {

    

    $('.carousel').slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 2000,
        responsive: [{
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    dots: true
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    dots: true
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    dots: true,
                    centerMode: true,
                    centerPadding: '20px',
                }
            }
        ]
    });

    $('nav.bun').click(function () {
        $(this).toggleClass('active');
        $('body').toggleClass('lock');
        $('.mobile-nav-interface').toggleClass('active');
    });

    $("[data-fancybox]").fancybox({
		// Options will go here
	});

    $('.item-data ul').conformity();

    $('.accordion .question').click(function () {
        $(this).siblings().next('.answer').slideUp(); 
        $(this).next('.answer').slideToggle();  
        $(this).find('.fa-angle-down').toggleClass('switch');
        $(this).siblings().find('.fa-angle-down').removeClass('switch');
    });

});

$(window).resize(function () {
    $('.item-data ul').conformity();
});