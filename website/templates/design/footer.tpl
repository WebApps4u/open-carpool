
            <div class="row text-center text-muted">
                <hr />
                <p>
                    <small>Open CarPool &copy; 2014</small>
                    |
                    <small><a href="http://www.opencarpool.org/4_1.html" target="_new">{t t='Site info'}</a></small>
                </p>
            </div>
        </div>

        {if $zendeskid}
            <!-- Zendesk Feedback Form Integration -->
            <script type="text/javascript" src="//assets.zendesk.com/external/zenbox/v2.6/zenbox.js"></script>
            <style type="text/css" media="screen, projection">
                @import url(//assets.zendesk.com/external/zenbox/v2.6/zenbox.css);
            </style>
            <script type="text/javascript">
                if (typeof(Zenbox) !== "undefined") {
                    Zenbox.init({
                        dropboxID:   "{$zendeskid}",
                        url:         "https://opencarpool.zendesk.com",
                        tabTooltip:  "Service",
                        tabImageURL: "https://assets.zendesk.com/external/zenbox/images/tab_service_right.png",
                        tabColor:    "black",
                        tabPosition: "Right"{if isset($user)},
                        requester_name: "{$user->name}",
                        requester_email: "{$user->email}"{/if}
                    });
                }
            </script>
        {/if} {* Zen Desk *}

    </body>
</html>