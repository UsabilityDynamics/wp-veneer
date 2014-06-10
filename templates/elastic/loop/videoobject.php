<div id="dynamic_filter" class="dynamic_filter df_element df_top_wrapper df_element df_top_wrapper clearfix" dynamic_filter="hdp_photo_gallery">
  <div class="df_element hdp_results clearfix">
    <!-- ko if: !videos.documents().length -->
    <ul class="df_element hdp_results_items">
      <li class="hdp_results_item">
        <ul class="clearfix">
          <li>No Videos found</li>
        </ul>
      </li>
    </ul>
    <!-- /ko -->
    <!-- ko if: videos.documents().length -->
    <ul data-bind="foreach: videos.documents" class="df_element hdp_results_items clearfix">

      <li class="hdp_results_item" data-bind="attr: {df_id_: _id}">
        <ul class="df_result_data">
          <li attribute_key="raw_html">
            <ul>
              <li>
                <ul class="hdp_photo clearfix">
                  <li class="hdp_photo_thumbnail">
                    <a data-bind="href:fields['url'],attr:{title:'Videos from '+fields['summary']}">
                      <div class="overlay"></div>
                      <img data-bind="attr:{src:fields['image.small']}" />
                    </a>
                  </li>
                  <li class="hdp_photo_title"><a data-bind="html:fields['summary'],attr:{href:fields['url'],title:'Videos from '+fields['summary']}"></a></li>
                  <li class="hdp_photo_date" data-bind="html:moment(fields.event_date[0]).format('LLLL')"></li>
                  <li class="hdp_photo_location" data-bind="html:(fields['venue.address.city']+', '+fields['venue.address.state'])"></li>
                </ul>
              </li>
            </ul>
          </li>
        </ul>
      </li>

    </ul>
    <!-- /ko -->

    <div data-bind="visible:videos.documents().length" class="hdp_results_message clearfix" style="display: block;">
      <div class="df_load_status left">
        Displaying <span class="df_current_count" data-bind="html:videos.count">0</span> of <span data-bind="html:videos.total"></span> Galleries
      </div>
      <a class="btn" data-scope="videos" data-bind="visible:videos.has_more_documents,filterShowMoreControl:{count:6}">
        <span>Show <em data-bind="html:videos.moreCount" class="df_more_count"></em> More</span>
      </a>
    </div>

  </div>
</div>