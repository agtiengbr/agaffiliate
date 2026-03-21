{extends file='page.tpl'}

{block name='page_title'}
    {l s='Affiliate Area' mod='agaffiliate'}
{/block}

{block name='page_content'}
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-success" style="display: none;" id="affiliate_success">
                <a href="#" class="close" onclick="$('.alert').hide()">&times;</a>
                {l s='Link copied successfully!' mod='agaffiliate'}
            </div>

            <div class="alert alert-danger" style="display: none;" id="affiliate_error">
                <a href="#" class="close" onclick="$('.alert').hide()">&times;</a>
                {l s='There was an error copying the link!' mod='agaffiliate'}
            </div>
        </div>

        <form id="agaffiliate_form">
            <div class="col-sm-12">
                <div class="form-group">
                    <p>{l s='You will see an icon in the right side of your screen in every page of our shop. You can share this link in your social media, blog or other channel. Every time this link is clicked and an order is finished you will earn a comission.' mod='agaffiliate'}</p>
                </div>
            </div>
        </form>
    </div>
{/block}