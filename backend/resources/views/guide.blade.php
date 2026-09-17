@php
    $githubUrl = 'https://github.com/farad-tech/berack';
    $sections = [
        'account' => 'Create an account',
        'site' => 'Add your website',
        'tag' => 'Install the tag',
        'verify' => 'Check your first visit',
        'analytics' => 'Read a journey',
        'troubleshooting' => 'Troubleshooting',
    ];
@endphp
<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Get started with Berack: add your website, install the tracking tag, and understand recorded visit journeys, tabs, and last recorded pages.">
    <meta name="theme-color" content="#f8faf9">
    <title>Berack Guide | From installation to your first journey</title>
    <link rel="stylesheet" href="{{ url('/assets/fonts/vazirmatn.css') }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/guide.css') }}?v={{ filemtime(public_path('css/guide.css')) }}">
    <script src="{{ asset('js/guide.js') }}?v={{ filemtime(public_path('js/guide.js')) }}" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<header class="header shell">
    <a class="brand" href="{{ url('/') }}" aria-label="Berack home"><span class="brand-mark" aria-hidden="true"><x-panel.icon name="arrow-trending-up" /></span>berack<span>.</span></a>
    <nav aria-label="Main navigation"><a href="{{ url('/') }}">Home</a><a class="desktop-link" href="{{ url('/guide') }}" aria-current="page">Guide</a><a class="desktop-link" href="{{ $githubUrl }}">GitHub <x-panel.icon name="arrow-up-right" /></a></nav>
    <a class="button small" href="{{ url('/panel') }}">Open panel <x-panel.icon name="arrow-up-right" /></a>
</header>
<main id="main">
    <div class="guide-intro-band"><div class="shell guide-intro">
        <div><p class="eyebrow">BERACK / THE GUIDE</p><h1>From your first tag<br>to your first journey.</h1><p>Connect your website. Follow its visits. Make sense of the paths in between.</p></div>
        <div class="guide-intro-meta"><x-panel.icon name="book-open" /><span>Berack getting started guide</span><span>Setup &middot; Installation &middot; Reports</span></div>
    </div></div>
    <div class="shell docs-layout">
        <aside class="docs-sidebar">
            <nav class="docs-nav" aria-label="On this page"><p class="eyebrow">ON THIS PAGE</p>
                @foreach ($sections as $id => $label)
                    <a href="#{{ $id }}"><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ $label }}</a>
                @endforeach
            </nav>
            <div class="docs-help"><x-panel.icon name="chat-bubble-left-right" /><h2>A question along the way?</h2><a href="mailto:contact@berack.com">Contact the creator <x-panel.icon name="arrow-up-right" /></a><a href="{{ $githubUrl }}">Browse the source <x-panel.icon name="arrow-up-right" /></a></div>
        </aside>
        <div class="docs-content">
            <section class="doc-section" id="account" aria-labelledby="account-title">
                <div class="doc-heading"><span class="doc-number">01</span><div><p class="eyebrow">YOUR WORKSPACE</p><h2 id="account-title">Create an account</h2></div></div>
                <p>Register with your name, email address, and password. Your workspace keeps your websites and their recorded journeys together. Already registered? Sign in to pick up where you left off.</p>
                <div class="doc-actions"><a class="button primary" href="{{ url('/panel/register') }}">Create free account <x-panel.icon name="arrow-right" /></a><a class="text-link" href="{{ url('/panel/login') }}">Sign in <x-panel.icon name="arrow-up-right" /></a></div>
            </section>
            <section class="doc-section" id="site" aria-labelledby="site-title">
                <div class="doc-heading"><span class="doc-number">02</span><div><p class="eyebrow">ONE KEY PER WEBSITE</p><h2 id="site-title">Add your website</h2></div></div>
                <p>In your panel, open <strong>Websites</strong> and choose <strong>Add website</strong>. Fill in these two fields:</p>
                <dl class="field-reference"><div><dt>Website name</dt><dd>A name you recognize, such as <code>My shop</code>.</dd></div><div><dt>Domain</dt><dd>Your website's hostname, such as <code>shop.example.com</code>.</dd></div></dl>
                <p>Save the website to get its own API key and installation tag. Each website has a separate report. Use the website selector in the sidebar to switch between them.</p>
                <a class="text-link" href="{{ route('panel.sites.create') }}">Add a website <x-panel.icon name="arrow-right" /></a>
            </section>
            <section class="doc-section" id="tag" aria-labelledby="tag-title">
                <div class="doc-heading"><span class="doc-number">03</span><div><p class="eyebrow">A SINGLE SCRIPT TAG</p><h2 id="tag-title">Install the Berack tag</h2></div></div>
                <p>Open <strong>Installation</strong> in your panel and select your website if prompted. Copy <strong>Your Berack tag</strong> and place it once in the shared HTML head, before <code>&lt;/head&gt;</code>, on every page you want to track.</p>
                <div class="code-example"><div class="snippet-toolbar"><span><x-panel.icon name="code-bracket" />HTML <span class="snippet-label">/ Example tag</span></span><button type="button" id="copy-tag" class="copy-button" aria-label="Copy example tag" title="Copy example tag" hidden><x-panel.icon name="clipboard-document" /></button></div><pre tabindex="0" aria-label="Example installation tag"><code id="example-tag">&lt;script async src="{{ url('/sdk/trk_your_site_api_key.js') }}"&gt;&lt;/script&gt;</code></pre></div>
                <div class="snippet-status" id="copy-status" role="status" aria-live="polite"></div>
                <p class="doc-note"><x-panel.icon name="information-circle" /><span>This is an example, not a working key. Use the complete tag from your site's panel; do not install the placeholder above.</span></p>
                <div class="install-tabs" role="tablist" aria-label="Website platform" hidden>
                    <button type="button" role="tab" id="tab-html" aria-controls="install-html" aria-selected="true" tabindex="0">HTML</button>
                    <button type="button" role="tab" id="tab-spa" aria-controls="install-spa" aria-selected="false" tabindex="-1">React / Vue / SPA</button>
                    <button type="button" role="tab" id="tab-wordpress" aria-controls="install-wordpress" aria-selected="false" tabindex="-1">WordPress</button>
                </div>
                <div class="install-panel" id="install-html"><h3>Plain HTML or a server-rendered site</h3><p>Paste the tag into the shared layout's <code>&lt;head&gt;</code>. In Laravel, this is your main Blade layout. With separate HTML files, add it to each page.</p></div>
                <div class="install-panel" id="install-spa"><h3>React, Vue, or another SPA</h3><p>Add the tag once to the HTML entry file, such as <code>index.html</code>. Do not add another copy on each route. The SDK observes URL changes from <code>pushState</code>, <code>replaceState</code>, hash changes, and back or forward navigation.</p></div>
                <div class="install-panel" id="install-wordpress"><h3>WordPress</h3><p>Add the tag to the site's global header using your header-script integration, or include it before <code>&lt;/head&gt;</code> in your child theme's <code>header.php</code>. Publish the change and clear any page cache.</p></div>
                <a class="text-link" href="{{ route('panel.installation') }}">Get your site's tag <x-panel.icon name="arrow-right" /></a>
            </section>
            <section class="doc-section" id="verify" aria-labelledby="verify-title">
                <div class="doc-heading"><span class="doc-number">04</span><div><p class="eyebrow">MAKE A TEST VISIT</p><h2 id="verify-title">Check your first visit</h2></div></div>
                <ol class="verification-steps"><li>Open the published website in one browser tab.</li><li>Visit two or three pages, then go back to a previous page.</li><li>Wait a few seconds for the SDK to send the recorded steps.</li><li>In Berack, select your website and open <strong>View journeys</strong>. Refresh the report and select your visit.</li></ol>
                <p>You should see the recorded pages in order, including the return to an earlier page. Open a second tab to check that its journey stays separate.</p>
                <div class="success-note"><x-panel.icon name="check-circle" /><span>New visits can appear as <strong>End undetermined</strong>. You do not need to wait 30 minutes to see their steps.</span></div>
            </section>
            <section class="doc-section" id="analytics" aria-labelledby="analytics-title">
                <div class="doc-heading"><span class="doc-number">05</span><div><p class="eyebrow">READ THE PATH, NOT JUST THE COUNT</p><h2 id="analytics-title">Understand a journey</h2></div></div>
                <p>Each visit in each tab has its own ordered path. Select a visit to see its entry page, recorded steps, timestamps in your browser's local timezone, and last recorded page.</p>
                <figure class="example-journey"><figcaption><span><x-panel.icon name="arrow-trending-up" />Example journey</span><span>1 visit &middot; 1 tab &middot; Local time</span></figcaption>
                    <ol class="guide-timeline">
                        <li><span class="timeline-node entry">01</span><div><strong>/</strong><span>Home <small class="entry-label">First recorded step</small></span></div><time>10:00:00</time></li>
                        <li><span class="timeline-node">02</span><div><strong>/collection</strong><span>Collection</span></div><time>10:00:18</time></li>
                        <li><span class="timeline-node">03</span><div><strong>/products/linen</strong><span>Product</span></div><time>10:00:46</time></li>
                        <li><span class="timeline-node last"><x-panel.icon name="flag" /></span><div><strong>/collection</strong><span>Collection <small class="return-label">Revisited page</small></span><small class="last-label">Last recorded step</small></div><time>10:01:12</time></li>
                    </ol>
                    <p class="figure-note">Illustrative data. This path ends with the last observation, not a confirmed browser exit.</p>
                </figure>
                <h3>Find the visits you need</h3><p>Filter by date range or visit status, or search for a page or browser ID. A matching page brings back the complete journey, so you can still see what happened before and after it. Clear filters when you want to return to all visits.</p>
                <dl class="report-reference">
                    <div><dt>End undetermined</dt><dd>Less than 30 minutes have passed since the last recorded navigation. This does not mean the person is still on the site.</dd></div>
                    <div><dt>Ended by inactivity</dt><dd>At least 30 minutes have passed without recorded navigation. The next navigation starts a new visit. Someone may still be reading the page during this interval.</dd></div>
                    <div><dt>Last recorded pages</dt><dd>This report groups the last pages of visits ended by the 30-minute rule. It does not establish why a visit stopped.</dd></div>
                    <div><dt>Between first &amp; last record</dt><dd>The elapsed time between the first and last recorded steps, not total reading time or confirmed time on the website.</dd></div>
                    <div><dt>Browser IDs</dt><dd>Browser identifiers are not people. Clearing storage, switching browsers, or using another device can change the identifier.</dd></div>
                    <div><dt>Missing recorded steps</dt><dd>A gap in the recorded sequence. Browser restrictions or network failures may leave incomplete paths; missing pages are not guessed.</dd></div>
                </dl>
                <p class="doc-note"><x-panel.icon name="information-circle" /><span>Older events without tab IDs are excluded from journeys. If the browser cannot coordinate tabs, each page load starts a separate visit rather than combining unrelated paths.</span></p>
            </section>
            <section class="doc-section" id="troubleshooting" aria-labelledby="troubleshooting-title">
                <div class="doc-heading"><span class="doc-number">06</span><div><p class="eyebrow">WHEN SOMETHING LOOKS OFF</p><h2 id="troubleshooting-title">Troubleshooting</h2></div></div>
                <div class="guide-faq">
                    <details><summary>No journeys appear <x-panel.icon name="plus" /></summary><div><p>Check that the published page contains the tag from the correct website, not the example key in this guide. Browse a few pages, wait a few seconds, then refresh the report and clear its filters.</p><p>In your browser's Network tools, check the site's <code>/sdk/...</code> request, the SDK asset, and requests to <code>/save-tracker</code>. A blocked script, a content security policy, or a failed network request can prevent recording.</p></div></details>
                    <details><summary>The script returns 404 <x-panel.icon name="plus" /></summary><div><p>Copy a fresh tag from <strong>Installation</strong>. The loader URL must contain an existing site's API key. If the loader succeeds but <code>/berack/v1.0/berack.min.js</code> returns 404 on a self-hosted instance, check that the built SDK is deployed at that public path.</p></div></details>
                    <details><summary>I see a CORS or connection error <x-panel.icon name="plus" /></summary><div><p>Check the failed request in the browser's Network tools. Use the tag from the Berack instance that hosts your account. On a self-hosted instance, confirm that the configured public URL is reachable and uses HTTPS when your website does.</p><p>The event endpoint is <code>/save-tracker</code>. Check its POST and OPTIONS responses, including errors from your hosting server or proxy. Do not disable browser security to hide the error.</p></div></details>
                    <details><summary>SPA navigation is missing <x-panel.icon name="plus" /></summary><div><p>Install the tag once in the HTML entry file. Check that route changes actually update the browser URL through the History API or a hash change. UI changes without a URL change are not separate page navigations.</p></div></details>
                    <details><summary>A visit ends while I am still reading <x-panel.icon name="plus" /></summary><div><p>The 30-minute rule measures time without recorded navigation, not mouse movement, scrolling, or reading. An ended visit is a grouping rule, not proof that someone closed the tab.</p></div></details>
                    <details><summary>My local test does not reach Berack <x-panel.icon name="plus" /></summary><div><p>The URL in the tag must be reachable from the browser running the test. On another device, <code>localhost</code> points to that device, not your development machine. Use the correct reachable address for your Berack instance and inspect failed network requests.</p></div></details>
                </div>
                <div class="guide-contact"><x-panel.icon name="chat-bubble-left-right" /><div><h3>Still need a hand?</h3><p>Share the failing request's status and a description of what you expected. Do not include your password or private visit data.</p><a class="text-link" href="mailto:contact@berack.com">contact@berack.com <x-panel.icon name="arrow-up-right" /></a></div></div>
            </section>
            <div class="guide-finish"><span class="feature-icon mint"><x-panel.icon name="check" /></span><div><h2>Your website. Its next journey.</h2><p>Head back to your workspace and follow the first recorded steps.</p></div><a class="button primary" href="{{ url('/panel') }}">Open panel <x-panel.icon name="arrow-right" /></a></div>
        </div>
    </div>
</main>
<footer class="shell footer guide-footer"><div><a class="brand" href="{{ url('/') }}">berack<span>.</span></a><p>Behaviour tracker. Built with a clear focus.</p></div><div class="contact"><span class="eyebrow">SAY HELLO TO THE CREATOR</span><a href="mailto:contact@berack.com">contact@berack.com <x-panel.icon name="arrow-up-right" /></a></div><nav aria-label="Footer navigation"><a href="{{ url('/') }}">Home</a><a href="{{ $githubUrl }}">GitHub</a><a href="{{ url('/panel') }}">Open panel</a></nav></footer>
</body>
</html>
