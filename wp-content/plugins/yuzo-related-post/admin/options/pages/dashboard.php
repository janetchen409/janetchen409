<?php
/**
 * @since   6.0         2019-04-15      Release
 * @since   6.0.9       2019-07-21      Best look
 * @since   6.0.9.4     2019-07-27      Validation to revoke license
 * @since   6.0.9.7     2019-08-01      Now you can remove the license for testing in Localhost
 * @since   6.0.9.83    2019-08-01      Now you can remove the license for testing in Localhost
 */
/*
|--------------------------------------------------------------------------
| Creation pages
|--------------------------------------------------------------------------
*/
if( ! is_admin() ) return;

$loader       = YUZO_URL . 'admin/assets/images/loader.gif';
$logo         = YUZO_URL . 'admin/assets/images/icon.png';
$version      = YUZO_VERSION;
$buttonrevoke = '<a href="#" class="btn-revoke-lic button" data-nonce="'. wp_create_nonce( 'revoke-this-license' ) .'">Revoke license on this website<span class="spinner"></span></a>';

$variable = <<<XYZ
<div class="yzp-wrapper">
    <div class="yzp-header-wrapper">
        <span class="yzp-logo-text"><img src='$logo' />uzo</span>
        <span class="yzp-subtitle">Free <span class="yzp-version">v.{$version}</span></span>
        <div class='yzp-logo'>The first plugin you must install in Wordpress</div>
    </div>
    <div class="yzp-body-wrapper">
        <div class="yzp-body">
            <h1>Welcome to the new <span>Yuzo <span class="yzp-brands-6">6</span> Related and List posts</span><a href="http://bit.ly/YuzoDonate4" target="_blank" class="yzp-button-donate button">Donate</a></h1>
            <p class="yzp-subdescripcion">
            A lightweight and effective way to show related and post list to increase the visits of your website
            </p>
            <h2 class="nav-tab-wrapper wp-clearfix">
                <a href="#tab-1" class="nav-tab nav-tab-1 nav-tab-active">About</a>
                <a href="#tab-2" class="nav-tab nav-tab-2">Release Notes</a>
                <a href="#tab-3" class="nav-tab nav-tab-3">Versus</a>
                <!--<a href="#tab-5" class="nav-tab">Quick Start</a>
                <a href="#tab-3" class="nav-tab">Documentation</a>
                <a href="#tab-4" class="nav-tab">Support</a>-->
            </h2>
            <div class="yzp-tabs-content">
                <div class="yzp-tab-content tab-1 tab-active">
                    Welcome to the exciting world of Yuzo. Where you can easily show interesting posts, related or any kind of lists to get the attention of the visitor. It is a very modern, easy and advanced plugin.
                    <div class="yzp-about-cols">
                        <div class="yzp--col">
                            <span class="yzp--icon"><i class="fa fa-check"></i></span>
                            <span class="yzp--title">Easy and intuitive</span>
                            <p class="yzp--text">All the options of Yuzo Related and List posts are made so that you can understand them very easily without the need of a manual, with only a few tests you will be able to obtain the desired result.</p>
                        </div>
                        <div class="yzp--col">
                            <span class="yzp--icon"><i class="fa fa-check"></i></span>
                            <span class="yzp--title">Counters of views and clicks</span>
                            <p class="yzp--text">One of the functions of always Yuzo was to post the views per each posts, now you can also do it with the clicks to measure results.</p>
                        </div>
                        <div class="yzp--col">
                            <span class="yzp--icon"><i class="fa fa-check"></i></span>
                            <span class="yzp--title">Multiple instance</span>
                            <p class="yzp--text">Now you can create several Yuzo and place them in different parts of your website with multi instance, besides each one has its own configuration.</p>
                        </div>
                        <div class="yzp--col">
                            <span class="yzp--icon"><i class="fa fa-check"></i></span>
                            <span class="yzp--title">Performance and speed</span>
                            <p class="yzp--text">I have a obsession with the web speed, I've got that Yuzo loads super fast consuming a minimum of resources to the server, this is something that I will continue improving.</p>
                        </div>
                    </div>
                </div>
                <div class="yzp-tab-content tab-2">
                    <div class="yzp-changelog-wrap">
                        <div class="yzp-changelog">
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version"->6.1.53</span> <span class="date">- 2020-02-09</span></li>

                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Tabulation and separation of some code fractions.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>CSS code improvements.</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version"->6.1.52</span> <span class="date">- 2020-02-09</span></li>

                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>When you deactivate the plugin you can respond to a feeback that will allow us to improve.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Small changes in the CSS and JS. [PRO]</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Now only show more slack post as long as you have the option to exclude post without image active. [PRO]</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Aesthetics improvements in the Backend. [PRO]</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>The 'Design' tab is now called 'Builder'.s</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Function corrections and class tabulations.</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version"->6.1.45</span> <span class="date">- 2020-01-25</span></li>

                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>Template Inline Default a gray shadow was added.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Better performance to show related posts.[PRO]</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Now load library files when it comes to shortcode, this to avoid errors when executing Yuzo through shortcode.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Aesthetics improvements in the Backend. [PRO]</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>It showed error when an editor entered, this was because it did not validate if the Yuzo menu exists for an editor user. This was corrected.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>When you showed an INLINE template this used to show several posts when it is only one, this was corrected.</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version"->6.1.43</span> <span class="date">- 2020-01-22</span></li>

                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>Slogan of the plugin within the administration 'The first plugin that you must download in Wordpress'.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>New action link in the list of plugins.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Small changes in the CSS and JS.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>In the later version when installing the plugin the view tables and click counters are not being installed, this new version should solve the problem.</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version"->6.1.42</span> <span class="date">- 2020-01-13</span></li>

                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>Stars in the footnote of Yuzo so you have quick access to write a review about the advantages of using the plugin.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Remove unnecessary code.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>New version of Database to update tables and fields of the plugin.</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version"->6.1.40</span> <span class="date">- 2020-01-12</span></li>

                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>Donation buttons were added to help improve the plugin.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Change of functions from private to public was made.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Improvements in internal styles.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Style improvements for post migration.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>The migration function does not work correctly, but this was corrected correctly.</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version"->6.1.39</span> <span class="date">- 2020-01-08</span></li>

                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>It was added in the Yuzo Pro link in the PRO label within the changelog.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Minor corrections in the code.</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version"->6.1.37</span> <span class="date">- 2020-01-07</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type update">Update</span>New database version, this helps to install the table to those who have not installed the click table from the beginning.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>If you have the shortcode <code>[yuzo]</code> without ID, then it will take the first active shortcode ID it finds.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Small tweaks in the changelog.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Within the preview the INLINE template was showing more than 1 post, this was corrected.</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.1.34</span> <span class="date">- 2020-01-06</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>The AJAX action of the preview was running on each page of the administrator, this should only be executed within the preview page, this was corrected.</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.1.33</span> <span class="date">- 2020-01-06</span></li>

                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>Now you can add an icon to view the views within the content.</li>

                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>The front scss added some lines for a possible icon in the future.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Update of the <code>readme.txt</code> file with new tags</li>

                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>When you had active not show post that have no image were hiding other yuzos, this was corrected.</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.1.31</span> <span class="date">- 2020-01-04</span></li>

                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>Now Yuzo shows the view counter at the top of the content by default, this will be a standard.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>New tabs were created to reorganize: General, SEO, View, Custom.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>Now the name of the plugin was added the word FREE to know how to identify the Pro version.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>New design, arranged colors for better reading and not cause visual disturbances.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>Now you can see which template is being used in Yuzos list.</li>

                                <li class="yzp-changelog-li"><span class="yzp--type update">Update</span>Framework <code>Pixel</code> was updated to the version <code>1.6.32</code></li>
                                <li class="yzp-changelog-li"><span class="yzp--type update">Update</span>New GEO tracker API to obtain visitor data by clicking inside Yuzo (freegeoip.app).</li>

                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Now the viewer is updated after typing in the field: text on and below yuzo.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Now there are 9 pre-set Yuzo.</li>

                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Now the click counter only appears when there is a value greater than 0.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>In the design tab you have new default values.</li>

                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Problems when viewing the counter in the block editor.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>The update is being mixed with the free version, in this version that update is separated.</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.1.2</span> <span class="date">- 23-12-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type awesome">🦄 Awesome</span>New stable version <code>6.1</code> with the Yuzo Lite released</li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>Now Yuzo statistics can be seen in Gutenberg</li>
                                <li class="yzp-changelog-li"><span class="yzp--type update">Update</span>Framework <code>Pixel</code> was updated to the version <code>1.6.31</code></li>

                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Now in the setting you can add customizable CSS</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>New List Template: X3,X4.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>The possibility of adding Text Above and Below Yuzo</li>

                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Improvements in the code: New logs added, data in the structure, others.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Better aesthetics of colors and text for better visualization</li>

                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Preview didn't update with all the changes, now it does</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.9.84</span> <span class="date">- 25-11-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type awesome">🦄 Awesome</span>Now Yuzo is compatible with Wordpress <code>5.3</code></li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>New option display_as_list_template is added: you can replace the loop of your archive and make it show Yuzo instead of the template</li>
                                <li class="yzp-changelog-li"><span class="yzp--type update">Update</span>Framework <code>Pixel</code> was updated to the version <code>1.5.8</code></li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Documentation some classes and functions of the code</li>

                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>New message in the metabox when there is no Yuzo created or active</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Name and organization of the admin menu of Yuzo was changed for a better understanding</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Reordering the code of many primary classes for better understanding</li>

                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Notice message corrections in the code</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.9.83</span> <span class="date">- 04-10-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span><code>Post Value</code> The calculation of the value of a post is in the BETA stage.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>New tab in the Yuzo Metabox, now you can add general post options.</li>

                                <li class="yzp-changelog-li"><span class="yzp--type update">Update</span>Framework <code>Pixel</code> was updated to the version <code>1.5.3</code>.</li>

                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>The option <code> Image Post </code> was added in the Yuzo Metabox, with this we will force as a priority to show that image in Yuzo.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Option added <code>Disabled this Yuzo</code> within the metabox, this to be able to deactivate Yuzo in specific posts.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>The Changelog link was added at the bottom of the Yuzo screens.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Constant <code>YUZO_VERSION_CHANGELOG</code> was added to force to look at Yuzo's changelog (occasionally).</li>

                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Improvements in tabulation (file) of plugi.n classes.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Now you can revoke the license in the activcaion popup, it serves to migrate from test sites to production with the same domain.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>The order of the submenus of Yuzo was changed for better interpretation.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>The save buttons on the footer were removed, now the top menu is sticky.</li>

                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Now if there is no active Yuzo result then it does not display the full widget.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>New internal validations to avoid a data interpretation error.</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.9.82</span> <span class="date">- 03-09-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type awesome">🦄 Awesome</span>Now the posts will have a value (costs) placed in a new column <code>(BETA)</code></li>
                                <li class="yzp-changelog-li"><span class="yzp--type update">Update</span>Framework <code>Pixel</code> was updated to the version <code>1.5.2</code></li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>The option <code>Show image</code> was added, at the moment it serves for the multicolored template.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Best internal design in the post builder.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>𝙼𝚒𝚗𝚒𝚏𝚒𝚎𝚍 𝚏𝚒𝚕𝚎𝚜 (𝚌𝚜𝚜/𝚓𝚜) 𝚏𝚘𝚛 𝚝𝚑𝚎 𝚋𝚊𝚌𝚔-𝚎𝚗𝚍 𝚊𝚗𝚍 𝚏𝚛𝚘𝚗𝚝-𝚎𝚗𝚍</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.9.81</span> <span class="date">- 29-08-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>The css for margin, padding and background of the yuzo wrap was added</li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>Post spacing added</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.9.8</span> <span class="date">- 28-08-2019</span></li>

                                <li class="yzp-changelog-li"><span class="yzp--type awesome">🦄 Awesome</span>New post design builder, now it is more practical and intuitive</li>

                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Documentation was added within the help tab in all menus of: General configuration and within each Yuzo</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Now Yuzo calculates better CPC for each click, this functionality is in BETA, it will soon be released.</li>

                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>4 new posts were added due to defects for better understanding (It only works when the plugin is installed)</li>

                                <li class="yzp-changelog-li"><span class="yzp--type update">Update</span>Library <code>phpConsole</code> was updated to the version <code>1.1</code></li>

                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Interpretation improvements and new messages for the Yuzo widget</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>New modified header of Setting General</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>New way of interpreting data from Yuzo columns</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Now you can better calculate the CPC of each click on the related ones thanks to the item level fields within the YuzoClicks table</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>𝙼𝚒𝚗𝚒𝚏𝚒𝚎𝚍 𝚏𝚒𝚕𝚎𝚜 (𝚌𝚜𝚜/𝚓𝚜) 𝚏𝚘𝚛 𝚝𝚑𝚎 𝚋𝚊𝚌𝚔-𝚎𝚗𝚍 𝚊𝚗𝚍 𝚏𝚛𝚘𝚗𝚝-𝚎𝚗𝚍</li>

                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Between version 6.0.9.7 and 6.0.9.8 there is a lot of difference with respect to the design, it was tried to create that these 2 are compatible in the transition.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>The filter of the manually added posts was not catching correctly, now if you grab them well.</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>The plugin in PHP5.6 version was tested and works correctly.</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.9.7</span> <span class="date">- 01-08-2019</span></li>

                                <li class="yzp-changelog-li"><span class="yzp--type awesome">🦄 Awesome</span>Yuzo now only works with PHP7. (to be able to enjoy all its functionalities at its maximum level)</li>

                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Added the option 'Categories to include' for relationship based on titles</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>New instructional message for the migration process, in order to have a better understanding</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Option 'Mode Debug': This is a review or developer mode, When activating this option, the results of the development tests or errors will be</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>The <code>phpConsole</code> library was added with this process can be tracked</li>

                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>The function <code>get_yuzo(YUZO_ID)</code> is enabled to be able to put it anywhere in the template</li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>Now you can remove the license for testing in Localhost</li>

                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Algorithm type 'title' has now improved, the ASC order that did it automatically was removed, also the filter to remove</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Change of name in the menu of <code>List/Related posts</code> to <code>Related/List posts</code></li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>𝙼𝚒𝚗𝚒𝚏𝚒𝚎𝚍 𝚏𝚒𝚕𝚎𝚜 (𝚌𝚜𝚜/𝚓𝚜) 𝚏𝚘𝚛 𝚝𝚑𝚎 𝚋𝚊𝚌𝚔-𝚎𝚗𝚍 𝚊𝚗𝚍 𝚏𝚛𝚘𝚗𝚝-𝚎𝚗𝚍</li>

                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>The query_posts function was being used for the final relationship, this caused another sql filter of other plugins could interfere, this was corrected and made purer with its own filter.</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.9.6</span> <span class="date">- 28-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>New design within Yuzo's list</li>

                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Argorithm improvements by category relationship</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Credits of the plugin in the footer of the custom post type Yuzo</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Remove new metabox inside the Yuzo panel that are not necessary</li>

                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Change in label 'Yuzo type' to 'Location', gives a better interpretation</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.9.5</span> <span class="date">- 27-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type update">Update</span>The SQL class is modified Library <code>sqlQueryBuilder</code> was updated to the version <code>1.2.1</code></li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Relationship by tag and most viewed did not work well, this was corrected</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.9.4</span> <span class="date">- 27-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Now you can revoke the license to be able to add it on another website</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Option in setting general <code>Exclude post without images</code>: If you activate this option Yuzo does not show post that do not have images, it will try to show the next post available with image, it does not always have a post next with image available</li>

                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Better validations to get an active Yuzo</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Database version update</li>

                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Shortcode was showing even while deactivating, this was corrected</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>In multi-site minor menu arrangements</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.9.3</span> <span class="date">- 25-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>A <code>note.php</code> file was added for the notes to be made, before it was in the main file</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>It was missing a <code>ECHO</code> in Yuzo's shortcode</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.9.2</span> <span class="date">- 24-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Settings on the counter, filters for robots were added</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>It showed an SQL statement when there were no results, this was removed</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.9.1</span> <span class="date">- 23-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Minimal corrections within the welcome code</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.9</span> <span class="date">- 21-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type awesome">🦄 Awesome</span>CPC: Cost per click, this gives an estimated value to your posts (BETA)</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Improvements in the post level of the plugin</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Documentation added some functions</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.8.4</span> <span class="date">- 19-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Removed filters in the counter of visits to have a cleaner counter</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Update framework</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>When there were no results in the algorithm and showed random post, exclusion was not considered</li>
                                <li class="yzp-changelog-li"><span class="yzp--type remove">Remove</span>Remove commented code no longer necessary</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.8.3</span> <span class="date">- 17-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Change in the view counter</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Update framework version 1.5.1</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.8.2</span> <span class="date">- 16-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>For the preview to have a better accuracy it must have the style of parent and child theme</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.8.2</span> <span class="date">- 16-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Several errors corrected from the preview</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>The style sheet for the preview is now published and not internal</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.8</span> <span class="date">- 15-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Added the option 'Disable only in the post' to disable the counter in that post</li>
                                <li class="yzp-changelog-li"><span class="yzp--type update">Update</span>The SQL class is modified <code>SqlQueryBuilder</code> for better performance. Version 1.2</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Added tooltip in the device icons to show the resolution</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Update framework version 1.5</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>View counter speed per post was accelerated to the maximum</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>It was adjusted that the tablet resolution now reaches 1024px<br /></li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>When there were no results in the algorithm and showed random post, exclusion was not considered</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.7</span> <span class="date">- 14-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Functionality for widget styles</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>New counters published in the classic wordpress editor</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Update framework version 1.5</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Validations of counters in the different post types allowed in various locations</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.6</span> <span class="date">- 13-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Counters of visits and clicks are shown in the target publication</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>If you disable the counters these will not show anywhere</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>The counters in the admin bar will only be displayed in the frontend, instead it is now displayed in the publication box</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.5</span> <span class="date">- 12-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>The option <code>Post Type</code> was added: 'Select the post type you want to count and show in the post list column'.
                                &It also lets you know in which post type account the post view and in which</li>
                                &nbsp;It also lets you know in which post type account the post view and in which</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Remove columns from other plugins in the custom post type yuzo</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Columns is validated where the counters can be displayed</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Remover metabox 'dpsp_share_statistics' infiltrado en yuzo</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Two columns as the default value for the post view in mobile</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>There is a character escaped at the time of installation</li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>Notice correction</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.4</span> <span class="date">- 12-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>A new order is added to the list [A-Z] and [Z-A]</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.3</span> <span class="date">- 12-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>Now you can import and export every Yuzo configuration</li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>Now you can migrate the post added manually from version 0.99 to 6.x</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Minified files (css/js) for the back-end and front-end</li>
                                <li class="yzp-changelog-li"><span class="yzp--type add">Newfeatures</span>Add the shortcode of views of the previous plugin and current</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Update framework version 1.4.8</li>
                                <li class="yzp-changelog-li"><span class="yzp--type improve">Improve</span>Functions that should only run on the backend or internal specific pages</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.2</span> <span class="date">- 12-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type fixed">Bugfixes</span>for the colors of links and text</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0.1</span> <span class="date">- 11-07-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type awesome">🦄 Awesome</span>The development of the plugin version 6.0 was finished and it was sent to the beta tester</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">6.0</span> <span class="date">- 11-04-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type new">RELEASE</span>Start of development of the new Yuzo renewed and with maximum security</li>
                            </ul>
                            <ul class="yzp-changelog-item">
                                <li class="yzp-changelog-li"><span class="version">5.12 (old version)</span> <span class="date">- 10-04-2019</span></li>
                                <li class="yzp-changelog-li"><span class="yzp--type">💔</span>Remove the previous version by small flaws</li>
                            </ul>
                        </div>
                        <div class="yzp-changelog-side">
                        </div>
                    </div>
                </div>
                <div class="yzp-tab-content tab-3">
                    <table class="yzp-table-compare">
                        <thead>
                            <tr>
                                <th align="left">Features</th>
                                <th align="center">Yuzo<span class="yzp-vs">vs</span></th>
                                <th align="center">Shareaholic</th>
                                <th align="center">Contextual</th>
                                <th align="center">Others Similar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td align="left">Advanced relationship algorithm</td>
                                <td align="center">✅</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                            </tr>
                            <tr>
                                <td align="left">Several layout</td>
                                <td align="center">✅</td>
                                <td align="center">✔️</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                            </tr>
                            <tr>
                                <td align="left">Rapid execution</td>
                                <td align="center">✅</td>
                                <td align="center">✔️</td>
                                <td align="center">➖</td>
                                <td align="center">❌</td>
                            </tr>
                            <tr>
                                <td align="left">Multi-instance</td>
                                <td align="center">✅</td>
                                <td align="center">✔️</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                            </tr>
                            <tr>
                                <td align="left">Query cache</td>
                                <td align="center">✅</td>
                                <td align="center">➖</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                            </tr>
                            <tr>
                                <td align="left">Relationship Filters</td>
                                <td align="center">✅</td>
                                <td align="center">➖</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                            </tr>
                            <tr>
                                <td align="left">Themes</td>
                                <td align="center">✅</td>
                                <td align="center">✔️</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                            </tr>
                            <tr>
                                <td align="left">Exclude advanced</td>
                                <td align="center">✅</td>
                                <td align="center">➖</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                            </tr>
                            <tr>
                                <td align="left">Include/Exclude within the post</td>
                                <td align="center">✅</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                            </tr>
                            <tr>
                                <td align="left">View counter</td>
                                <td align="center">✅</td>
                                <td align="center">➖</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                            </tr>
                            <tr>
                                <td align="left">Customizable Text and box</td>
                                <td align="center">✅</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                            </tr>
                            <tr>
                                <td align="left">Level article</td>
                                <td align="center">✅</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                            </tr>
                            <tr>
                                <td align="left">SEO friendly</td>
                                <td align="center">✅</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                                <td align="center">❌</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

XYZ;
$imagen = [
	'https://i.imgur.com/SFIgSmV.jpg','https://i.imgur.com/SygStno.jpg','https://i.imgur.com/7iMtzX3.jpg',
	'https://i.imgur.com/GGvrpzn.jpg','https://i.imgur.com/7B1FFGU.jpg','https://i.imgur.com/OfXQuix.jpg',
	'https://i.imgur.com/Qj4W84O.jpg','https://i.imgur.com/C7T6SvH.jpg','https://i.imgur.com/jFcViTG.jpg',
	'https://i.imgur.com/bzZq35o.jpg',
];
$fivestart = '<div class="fdc-fives fdc-tooltip top"><a href="http://bit.ly/Yuzo5Star" target="_blank" style="text-decoration: none">
<span class="dashicons dashicons-wordpress" style="color:black"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
</a>
<span class="tiptext"><img src="'. $imagen[rand(0,9)] .'.jpg" />Show us some 💙 by writing your review</span>
</div>';
PF::addPage( YUZO_ID . '-page-quick-start' , array(
    'menu_title'     => 'Yuzo <span>Free version</span>',
    'menu_slug'      => 'yuzo',
    'menu_icon'      => YUZO_URL . 'admin/assets/images/icon.png',
    'menu_title_sub' => __('About', 'yuzo'),
    'page_html'      => $variable,
    'footer_credit'  => 'Made with 💙 by <span class="yzp-admin-credit">Lenin Zapata</span><span class="fdc-admin-footer-separate">|</span>' . $fivestart,
));