$(document).ready(() => {
    const generateLink = () => {
        let url = `${window.location.href}`;
        let token = `?affiliate_token=${affiliate_token}`;
        return (url.indexOf('#') != -1) ? url.replace('#', `${token}`) : `${url}${token}`;
    }

    const affiliateBox = () => {
        const body = $('body');
        return body.prepend(`
            <div class="overlay"></div>
            <div id="label">
                <i class="agaffiliate-fab agaffiliate-icon"><img src="${agaffiliate_url}modules/agaffiliate/views/img/bullhorn.svg"></i>
            </div>
            <div class="agaffiliate-wrapper">
                <div class="agaffiliate-head-text">
                    <div align="right">
                        <i class="agaffiliate-fas agaffiliate-icon"><img src="${agaffiliate_url}modules/agaffiliate/views/img/close.svg"></i>
                    </div>
                </div>
                <form class="agaffiliate-box" id="agaffiliate_form_user">
                    <div class="agaffiliate-desc-text">
                        <p>${agaffiliate_translate['info']}</p>
                        <div class="col-sm-12">
                            <div class="alert alert-success ag-alert" style="display: none;" id="affiliate_success">
                                <a href="#" class="close" onclick="$('.alert').hide()">&times;</a>
                                ${agaffiliate_translate['success']}
                            </div>

                            <div class="alert alert-danger ag-alert" style="display: none;" id="affiliate_error">
                                <a href="#" class="close" onclick="$('.alert').hide()">&times;</a>
                                ${agaffiliate_translate['error']}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="affiliate_name" class="required">${agaffiliate_translate['label']}</label>
                            <input class="form-control" type="text" name="affiliate_link" id="affiliate_link" value="" required="" readonly="">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <button type="button" class="btn btn-primary" id="affiliate_btn">${agaffiliate_translate['button']}</button>
                        </div>
                    </div>
                </form>
            </div>
        `);
    }
    const alert = (type) => {
        const message = $(`#affiliate_${type}`);
        return message.css('display', 'block');
    }
    $(document).on('click', '#affiliate_btn', () => {
        let input = $('#affiliate_link');
        input.select();
        (document.execCommand('copy')) ? alert('success'): alert('error');
        return false;
    });
    $(document).on('click', '.agaffiliate-icon', () => {
        let wrapper = $('.agaffiliate-wrapper');
        let overlay = $('.overlay');
        let label = $('#label');
        let ag_alert = $('.ag-alert');
        let affiliate_link = $('#affiliate_link');
        
        if (wrapper.hasClass('agaffiliate-wrapper-open')) {
            wrapper.removeClass('agaffiliate-wrapper-open');
            label.css('display', 'block');
            overlay.css('display', 'none');
            affiliate_link.val('');
        } else {
            wrapper.addClass('agaffiliate-wrapper-open');
            overlay.css('display', 'block');
            label.css('display', 'none');
            ag_alert.css('display', 'none');
            affiliate_link.val(generateLink());
        }
    });
    if (agaffiliate_config == true && !agaffiliate_id) {
        affiliateBox();
    }
});