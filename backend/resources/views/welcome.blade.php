@php($githubUrl = 'https://github.com/farad-tech/berack')
<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Follow every website visit, page by page. Berack is an open-source journey tracker for understanding where visits start and their last recorded page.">
    <meta name="theme-color" content="#eef3ef">
    <title>Berack | Every visit has a path</title>
    <link rel="stylesheet" href="{{ url('/assets/fonts/vazirmatn.css') }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
    <script src="{{ asset('js/landing.js') }}?v={{ filemtime(public_path('js/landing.js')) }}" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<header class="header shell">
    <a class="brand" href="{{ url('/') }}" aria-label="Berack home"><span class="brand-mark" aria-hidden="true"><x-panel.icon name="arrow-trending-up" /></span>berack<span>.</span></a>
    <nav aria-label="Main navigation"><a class="desktop-link" href="#how-it-works">How it works</a><a href="{{ url('/guide') }}">Guide</a><a class="desktop-link" href="{{ $githubUrl }}">GitHub <x-panel.icon name="arrow-up-right" /></a></nav>
    <a class="button small" href="{{ url('/panel') }}">Open panel <x-panel.icon name="arrow-up-right" /></a>
</header>
<main id="main">
    <section class="hero" aria-labelledby="hero-title">
        <div class="shell hero-copy">
            <p class="eyebrow"><span class="status-dot"></span> OPEN-SOURCE WEBSITE JOURNEY TRACKER</p>
            <h1 id="hero-title">Berack<span>.</span></h1>
            <p class="hero-lead">Every visit has a path.<br>See where it goes.</p>
            <p class="hero-description">From the first page to the last recorded step.<br class="desktop-link"> Understand the journey, not just the page count.</p>
            <div class="hero-actions"><a class="button primary" href="{{ url('/panel/register') }}">Create free account <x-panel.icon name="arrow-right" /></a><a class="text-link" href="{{ url('/guide') }}">Read the guide <x-panel.icon name="arrow-up-right" /></a></div>
        </div>
        <div class="journey-demo shell">
            <div class="demo-toolbar"><span class="eyebrow">A VISIT, PAGE BY PAGE</span><div class="visit-selector" role="group" aria-label="Example visit"><button type="button" aria-pressed="true" data-visit="shop">Shop visit</button><button type="button" aria-pressed="false" data-visit="docs">Docs visit</button></div></div>
            <canvas id="journey-canvas" role="img" aria-label="Example shop visit: home, collection, product, cart, then product as the last recorded page."></canvas>
            <div class="demo-caption"><span>ILLUSTRATIVE DATA</span><span id="visit-summary" aria-live="polite">5 recorded steps / 1 visit / 1 tab</span></div>
            <noscript><p>Example visit: Home &rarr; Collection &rarr; Product &rarr; Cart &rarr; Product. Last recorded page: Product.</p></noscript>
        </div>
    </section>
    <section class="principles section shell" aria-labelledby="principles-title">
        <div class="section-heading"><p class="eyebrow">LESS NOISE. MORE CONTEXT.</p><h2 id="principles-title">The space between<br>arrival and departure.</h2><p>A page count tells you what was viewed. A journey shows you what happened next.</p></div>
        <div class="principle-grid">
            <article><span class="feature-icon mint"><x-panel.icon name="arrow-trending-up" /></span><h3>Keep the whole story</h3><p>Follow recorded pages in order, including return visits to a page and navigation without a full reload.</p><span class="detail-label">ENTRY &rarr; STEPS &rarr; LAST PAGE</span></article>
            <article><span class="feature-icon lilac"><x-panel.icon name="squares-2x2" /></span><h3>Separate every journey</h3><p>Visits and browser tabs stay separate. A return tomorrow does not become an extension of today's path.</p><span class="detail-label">ONE VISIT. ONE TAB. ONE PATH.</span></article>
            <article><span class="feature-icon coral"><x-panel.icon name="flag" /></span><h3>Know where the trail stops</h3><p>See the last recorded page. A visit ends after 30 minutes without recorded navigation, not a guessed browser exit.</p><span class="detail-label">OBSERVATIONS, NOT ASSUMPTIONS</span></article>
        </div>
    </section>
    <section class="setup-band" id="how-it-works" aria-labelledby="setup-title"><div class="shell section setup-layout">
        <div class="section-heading"><p class="eyebrow">FROM WEBSITE TO WORKSPACE</p><h2 id="setup-title">A small tag.<br>A clearer picture.</h2><p>No custom events to configure. Just the page-by-page paths your visitors leave behind.</p><a class="text-link" href="{{ url('/guide') }}">Installation guide <x-panel.icon name="arrow-up-right" /></a></div>
        <ol class="setup-steps">
            <li><span class="step-number">01</span><div><h3>Make it your workspace</h3><p>Create an account and add your website's name and domain.</p></div><x-panel.icon name="globe-alt" /></li>
            <li><span class="step-number">02</span><div><h3>Add the Berack tag</h3><p>Copy your site's installation tag from the panel into your website. Each site has its own API key.</p></div><x-panel.icon name="code-bracket" /></li>
            <li><span class="step-number">03</span><div><h3>Follow the first visit</h3><p>Open your website, browse a few pages, then explore the recorded journey in your panel.</p></div><x-panel.icon name="arrow-trending-up" /></li>
        </ol>
    </div></section>
    <section class="shell section open-section" aria-labelledby="open-title">
        <div><p class="eyebrow">OPEN SOURCE. OPEN TO IDEAS.</p><h2 id="open-title">Understand your visitors.<br>Understand your tools.</h2><p>Read the source, run your own instance, or help shape what comes next. Berack keeps its focus on one thing: website visit journeys.</p><a class="button" href="{{ $githubUrl }}">Explore on GitHub <x-panel.icon name="arrow-up-right" /></a></div>
        <div class="project-note"><span class="eyebrow">A NOTE ON THE DATA</span><h3>A browser is not a person.</h3><p>Browser identifiers help connect recorded steps. They do not identify a unique person, and the last recorded page does not prove someone closed your site.</p><a class="text-link" href="{{ url('/guide') }}">Get to know Berack <x-panel.icon name="arrow-right" /></a></div>
    </section>
    <section class="cta-band"><div class="shell cta-content"><div><p class="eyebrow">YOUR NEXT VISIT IS A STORY.</p><h2>Start following the path.</h2></div><a class="button primary" href="{{ url('/panel/register') }}">Create free account <x-panel.icon name="arrow-right" /></a></div></section>
</main>
<footer class="shell footer"><div><a class="brand" href="{{ url('/') }}">berack<span>.</span></a><p>Behaviour tracker. Built with a clear focus.</p></div><div class="contact"><span class="eyebrow">SAY HELLO TO THE CREATOR</span><a href="mailto:contact@berack.com">contact[at]berack.com <x-panel.icon name="arrow-up-right" /></a></div><nav aria-label="Footer navigation"><a href="{{ url('/guide') }}">Guide</a><a href="{{ $githubUrl }}">GitHub</a><a href="{{ url('/panel') }}">Open panel</a></nav></footer>
</body>
</html>
