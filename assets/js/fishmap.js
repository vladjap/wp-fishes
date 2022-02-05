jQuery( function( $ ) {
    $(document).ready(function() {
        $('.fishmap-tabs-header-item').on('click', function(e) {
            if (!$(this).hasClass('fishmap-tab-header-item-active')) {

                // handle tabs
                $('.fishmap-tabs-header-item').removeClass('fishmap-tab-header-item-active');
                $(this).addClass('fishmap-tab-header-item-active')

                // handle content
                $('.fishmap-tab-content').removeClass('fishmap-tab-content-active');
                $('[data-tab-content-id="'+$(this).attr('data-tab-id')+'"]').addClass('fishmap-tab-content-active');

            }
        });

        $( ".fishmap-tabs-header-item" )
            .mouseenter(function() {
                if (!$(this).hasClass('fishmap-tab-header-item-active')) {
                    $('.fishmap-tabs-header-item').removeClass('fishmap-tab-header-item-active');
                    $(this).addClass('fishmap-tab-header-item-active')

                    $('.fishmap-tab-content').removeClass('fishmap-tab-content-active');
                    $('[data-tab-content-id="'+$(this).attr('data-tab-id')+'"]').addClass('fishmap-tab-content-active');
                }
            });

        $('.fishmap-selected-fish-bullet-a').on('click', function (e) {
            e.preventDefault();
        });

        $('.fishmap-rule-tables-table thead').on('click', function () {
            // console.log(this.parentElement, 'this.parentElement');
            if ($(this.parentElement).hasClass('fishmap-rule-table-active')) {
                $(this.parentElement.querySelector('tbody')).slideUp();
                $(this.parentElement).removeClass('fishmap-rule-table-active');
                // $('.fishmap-rule-table-active').removeClass('fishmap-rule-table-active');
            } else {
                $(this.parentElement.querySelector('tbody')).slideDown();
                $(this.parentElement).addClass('fishmap-rule-table-active');
            }


        })
    });
});