<div class="modal modal-image-upload" id="window_upload_img">
    <header class="modal-header">
        <h3>{$aLang.uploadimg}</h3>
        <a href="#" class="close jqmClose material-icons">close</a>
    </header>

    <div class="modal-content">
        <ul class="nav nav-pills nav-pills-tabs">
            <li class="active js-block-upload-img-item" data-type="pc"><a href="#">{$aLang.uploadimg_from_pc}</a></li>
            <li class="js-block-upload-img-item" data-type="link"><a href="#">{$aLang.uploadimg_from_link}</a></li>
        </ul>

        <form method="POST" action="" enctype="multipart/form-data" id="block_upload_img_content_pc"
              onsubmit="return false;" class="tab-content js-block-upload-img-content" data-type="pc">
            <p><label for="img_file">{$aLang.uploadimg_file}:</label>
                <input type="file" multiple name="img_file[]" id="img_file" value=""
                       class="input-text input-width-full"/></p>

            {hook run="uploadimg_source"}

            <p>
                <label for="form-image-align">{$aLang.uploadimg_align}:</label>
                <select name="align" id="form-image-align" class="input-width-full">
                    <option value="">{$aLang.uploadimg_align_no}</option>
                    <option value="left">{$aLang.uploadimg_align_left}</option>
                    <option value="right">{$aLang.uploadimg_align_right}</option>
                    <option value="center">{$aLang.uploadimg_align_center}</option>
                </select>
            </p>

            <span class="checkbox">
                <span>
                    <input type="checkbox" tabindex="" name="img_spoil" id="img_spoil" checked>
                    <label for="img_spoil">Спойлер</label>
                </span>
            </span>

            <p><label for="form-image-title">{$aLang.uploadimg_title}:</label>
                <input type="text" name="title" id="form-image-title" value="" class="input-text input-width-full"/></p>

            {hook run="uploadimg_additional"}

            <button type="submit" id="block_upload_img_content_pc_submit" class="button button-primary"
                    onclick="ls.ajax.ajaxUploadImg('block_upload_img_content_pc','{$sToLoad}');">{$aLang.uploadimg_submit}</button>
            <button type="submit" class="button jqmClose">{$aLang.uploadimg_cancel}</button>
        </form>


        <form method="POST" action="" enctype="multipart/form-data" id="block_upload_img_content_link"
              onsubmit="return false;" style="display: none;" class="tab-content js-block-upload-img-content"
              data-type="link">
            <p><label for="img_file">{$aLang.uploadimg_url}:</label>
                <input type="text" name="img_url" id="img_url" value="http://" class="input-text input-width-full"/></p>

            <p>
                <label for="form-image-url-align">{$aLang.uploadimg_align}:</label>
                <select name="align" id="form-image-url-align" class="input-width-full">
                    <option value="">{$aLang.uploadimg_align_no}</option>
                    <option value="left">{$aLang.uploadimg_align_left}</option>
                    <option value="right">{$aLang.uploadimg_align_right}</option>
                    <option value="center">{$aLang.uploadimg_align_center}</option>
                </select>
            </p>

            <span class="checkbox">
                <span>
                    <input type="checkbox" tabindex="" name="img_url_spoil" id="img_url_spoil" checked>
                    <label for="img_url_spoil">Спойлер</label>
                </span>
            </span>

            <p><label for="form-image-url-title">{$aLang.uploadimg_title}:</label>
                <input type="text" name="title" id="form-image-url-title" value="" class="input-text input-width-full"/>
            </p>

            {hook run="uploadimg_link_additional"}

            <button type="submit" id="block_upload_img_content_link_submit" class="button button-primary"
                    onclick="ls.ajax.ajaxUploadImg('block_upload_img_content_link','{$sToLoad}');">{$aLang.uploadimg_link_submit_load}</button>
            {$aLang.or}
            <button type="submit" class="button button-primary"
                    onclick="ls.topic.insertImageToEditor(jQuery('#img_url').val(), $('#img_url_spoil').prop('checked'), jQuery('#form-image-url-align').val(),jQuery('#form-image-url-title').val());">{$aLang.uploadimg_link_submit_paste}</button>
            <button type="submit" class="button jqmClose">{$aLang.uploadimg_cancel}</button>
        </form>
    </div>
</div>
	
