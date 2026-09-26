@php
    $githubUrl = 'https://github.com/farad-tech/berack';
    $siteUrl = url('/');
    $pageTitle = 'Berack | Open-source website visit tracking';
    $pageDescription = 'Berack is an open-source website analytics platform for tracking visitor journeys, ordered page views, browser tabs, and the last recorded page with a lightweight JavaScript SDK.';
    $faqItems = [
        ['question' => 'What is Berack?', 'answer' => 'Berack is an open-source website analytics platform that records visitor journeys page by page. It shows where a visit starts, which pages it follows, and the last recorded page.'],
        ['question' => 'What does Berack track?', 'answer' => 'Berack tracks page views and URL navigation, including back and forward navigation and single-page application route changes. It keeps visits and browser tabs as separate journeys.'],
        ['question' => 'Does Berack identify individual people?', 'answer' => 'No. Berack uses a browser identifier to connect recorded steps, but a browser identifier is not a verified person or identity.'],
        ['question' => 'How does Berack decide when a visit ends?', 'answer' => 'A visit is considered ended after 30 minutes without recorded navigation. This is an inactivity rule and does not prove that someone closed the website.'],
        ['question' => 'How do I install Berack on my website?', 'answer' => 'Create an account, add your website, open Installation in the panel, and place the generated Berack JavaScript tag in your shared HTML head.'],
    ];
    $structuredData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => $siteUrl.'#organization',
                'name' => 'Berack',
                'url' => $siteUrl,
                'sameAs' => [$githubUrl],
                'email' => 'contact@berack.com',
            ],
            [
                '@type' => 'WebSite',
                '@id' => $siteUrl.'#website',
                'url' => $siteUrl,
                'name' => 'Berack',
                'description' => $pageDescription,
                'publisher' => ['@id' => $siteUrl.'#organization'],
                'inLanguage' => 'en',
            ],
            [
                '@type' => 'WebPage',
                '@id' => $siteUrl.'#webpage',
                'url' => $siteUrl,
                'name' => $pageTitle,
                'description' => $pageDescription,
                'isPartOf' => ['@id' => $siteUrl.'#website'],
                'about' => ['@id' => $siteUrl.'#application'],
                'inLanguage' => 'en',
            ],
            [
                '@type' => 'SoftwareApplication',
                '@id' => $siteUrl.'#application',
                'name' => 'Berack',
                'applicationCategory' => 'BusinessApplication',
                'applicationSubCategory' => 'Website analytics',
                'operatingSystem' => 'Web',
                'url' => $siteUrl,
                'description' => $pageDescription,
                'isAccessibleForFree' => true,
                'license' => 'https://www.apache.org/licenses/LICENSE-2.0',
                'codeRepository' => $githubUrl,
                'publisher' => ['@id' => $siteUrl.'#organization'],
            ],
            [
                '@type' => 'FAQPage',
                '@id' => $siteUrl.'#faq',
                'url' => $siteUrl.'#faq',
                'mainEntity' => array_map(fn (array $item) => [
                    '@type' => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'],
                    ],
                ], $faqItems),
            ],
        ],
    ];
@endphp
<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $siteUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $siteUrl }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:site_name" content="Berack">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="theme-color" content="#eef3ef">
    <title>{{ $pageTitle }}</title>
    <script type="application/ld+json">@json($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    <link rel="stylesheet" href="{{ url('/assets/fonts/vazirmatn.css') }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/landing-seo.css') }}?v={{ filemtime(public_path('css/landing-seo.css')) }}">
    <script src="{{ asset('js/landing.js') }}?v={{ filemtime(public_path('js/landing.js')) }}" defer></script>
    <script async src="https://berack.ir/sdk/trk_V0j6b0Q9Q5wUMcluubbQvJfzPlfYMJb6HHMGE3u26Nr8Jyxo.js"></script>
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
            <p class="eyebrow"><span class="status-dot"></span> OPEN-SOURCE WEBSITE ANALYTICS</p>
            <h1 id="hero-title">Berack<span>.</span></h1>
            <p class="hero-lead">Understand every website visit.<br>See where it goes.</p>
            <p class="hero-description">Open-source visitor journey analytics, from the first page to the last recorded step.<br class="desktop-link"> Understand the journey, not just the page count.</p>
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
    <section class="seo-section" aria-labelledby="seo-title"><div class="shell section">
        <div class="section-heading"><p class="eyebrow">BUILT FOR CLEARER WEBSITE ANALYTICS</p><h2 id="seo-title">Track the path behind every page view.</h2><p>Berack gives developers, product teams, and site owners the context that ordinary traffic counters miss.</p></div>
        <div class="seo-grid">
            <article><h3>Website visit tracking</h3><p>See the ordered route from entry page to last recorded page, including revisits and single-page application navigation.</p></article>
            <article><h3>Self-hosted analytics</h3><p>Run the Laravel backend yourself, keep each website in its own workspace, and inspect the source on GitHub.</p></article>
            <article><h3>Lightweight JavaScript SDK</h3><p>Install one tag for page view tracking without session replay, heatmaps, or a large analytics suite.</p></article>
        </div>
    </div></section>
    <section class="faq-section" id="faq" aria-labelledby="faq-title"><div class="shell section">
        <div class="section-heading"><p class="eyebrow">COMMON QUESTIONS</p><h2 id="faq-title">A clearer answer before you start.</h2><p>Learn what Berack records, how journeys are separated, and what the last recorded page really means.</p></div>
        <div class="faq-list">
            @foreach ($faqItems as $item)
                <details><summary>{{ $item['question'] }} <x-panel.icon name="plus" /></summary><p>{{ $item['answer'] }}</p></details>
            @endforeach
        </div>
    </div></section>
    <section class="cta-band"><div class="shell cta-content"><div><p class="eyebrow">YOUR NEXT VISIT IS A STORY.</p><h2>Start following the path.</h2></div><a class="button primary" href="{{ url('/panel/register') }}">Create free account <x-panel.icon name="arrow-right" /></a></div></section>
</main>
<footer class="shell footer"><div><a class="brand" href="{{ url('/') }}">berack<span>.</span></a><p>Behaviour tracker. Built with a clear focus.</p></div><div class="contact"><span class="eyebrow">SAY HELLO TO THE CREATOR</span><a href="mailto:contact@berack.com">contact[at]berack.com <x-panel.icon name="arrow-up-right" /></a></div><nav aria-label="Footer navigation"><a href="{{ url('/guide') }}">Guide</a><a href="{{ $githubUrl }}">GitHub</a><a href="{{ url('/panel') }}">Open panel</a></nav></footer>
</body>
</html>
