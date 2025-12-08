<?php
// Load bookmarks from database
$bookmarksHtml = '';
if (isset($mysqli) && $mysqli) {
    $result = $mysqli->query("SELECT setting_value FROM site_settings WHERE setting_key = 'bookmarks_html'");
    if ($result && $row = $result->fetch_assoc()) {
        $bookmarksHtml = $row['setting_value'];
    }
}

// Parse bookmarks from HTML - each <p> contains [URL] Display Text
$bookmarks = [];
if (!empty($bookmarksHtml)) {
    // Extract text from each <p> tag
    preg_match_all('/<p[^>]*>(.*?)<\/p>/s', $bookmarksHtml, $matches);
    foreach ($matches[1] as $line) {
        $line = strip_tags($line);
        $line = trim($line);
        if (empty($line)) continue;

        // Parse [URL] Display Text format
        if (preg_match('/^\[(https?:\/\/[^\]]+)\]\s*(.+)$/i', $line, $parts)) {
            $bookmarks[] = [
                'url' => $parts[1],
                'text' => trim($parts[2])
            ];
        }
    }
}

// Fallback to defaults if no bookmarks in database
if (empty($bookmarks)) {
    $bookmarks = [
        ['url' => 'https://www.anthropic.com', 'text' => 'Anthropic'],
        ['url' => 'https://designvault.io/', 'text' => 'Design Vault']
    ];
}
?>
<!-- About Us Modal -->
<div class="modal modal--fullscreen" id="aboutUsModal">
  <div class="modal__overlay modal__overlay--about">
    <button class="modal__close" id="aboutUsModalClose">close window</button>
    <div class="about-overlay-content">
      <div class="about-header">
        <h2 class="modal__logo-text">crbntyp</h2>
        <p class="about-subtitle">Naturally gifted designer, with a mathmatical loaf...</p>
      </div>

      <div class="about-grid">
        <!-- Column 1: About Me -->
        <div class="about-column">
          <h3 class="about-column__title">About</h3>
          <div class="about-column__content">
            <p class="about-tagline">Engineering Manager with a Design Heart &amp; Technical Mind</p>
            <p>You don't survive 25 years in the tech industry by standing still. I am an autodidact to my core—I have spent my career pivoting, learning, and evolving alongside the technology itself. I don't just manage the process; I have learned every role within it from scratch.</p>

            <p class="about-section-title">The Design &amp; Engineering Lens (1999–2013)</p>
            <p>My career began when the web was young and roles weren't siloed. Back then, UX, UI, and content were all one job—if you designed it, you built it. At Magnett Systems I did exactly that, eventually growing into a Head of Design role at i3 Digital and Engage. That decade of doing everything taught me how to deliver award-winning work from concept to code.</p>

            <p class="about-section-title">The Architecture Lens (2013–2018)</p>
            <p>As the industry matured and specialised, so did I. At the BBC and Rapid7, I evolved into a UI Engineer and Architect—bridging design and development at scale. I understand the constraints my team faces today because I spent years solving them myself.</p>

            <p class="about-section-title">The Leadership Lens (Present)</p>
            <p>Today, I lead multi-stack engineering teams at Rapid7. Being self-taught has made me a more empathetic leader; I know how hard it is to master a new skill. I focus on inspiring junior engineers, fostering psychological safety, and encouraging my team to own their own growth.</p>
          </div>
        </div>

        <!-- Column 2: Projects -->
        <div class="about-column">
          <h3 class="about-column__title">Projects I've worked on</h3>
          <div class="about-column__content">
            <ul class="about-work-list">
              <li><span class="project-year project-year--present">2023 - PRESENT</span>Cloud Dev: Agent, Antivirus, VR Forensics &amp; Security Orchestration, Automation, and Response</li>
              <li><span class="project-year">2022</span> R7 Design System MUI</li>
              <li><span class="project-year">2020</span> R7 Design System React</li>
              <li><span class="project-year">2018</span> R7 Design System</li>
              <li><span class="project-year">2018</span> BBC Archive internal and external</li>
              <li><span class="project-year">2017</span> Easons</li>
              <li><span class="project-year">2017</span> Henderson Foodservice</li>
              <li><span class="project-year">2017</span> IGB Barking Buzz Betting App</li>
              <li><span class="project-year">2017</span> PSNI</li>
              <li><span class="project-year">2017</span> Permanent TSB</li>
              <li><span class="project-year">2016</span> MCS Group</li>
              <li><span class="project-year">2015</span> Irish Ferries</li>
              <li><span class="project-year">2014</span> IGB Barking Buzz</li>
              <li><span class="project-year">2014</span> Power NI</li>
              <li><span class="project-year">2014</span> Telestack</li>
              <li><span class="project-year">2013</span> Travel Department</li>
              <li><span class="project-year">2011</span> SafeFood</li>
              <li><span class="project-year">2010</span> Board Gais Networks</li>
              <li><span class="project-year">2010</span> Derry City Council</li>
              <li><span class="project-year">2010</span> Dublin Bus</li>
              <li><span class="project-year">2010</span> Northern Ireland Assembly</li>
              <li><span class="project-year">2010</span> Police Ombudsman</li>
              <li><span class="project-year">2009</span> Brett Martin</li>
              <li><span class="project-year">2008</span> B&B Ireland</li>
              <li><span class="project-year">2008</span> Failte Ireland Corporate</li>
              <li><span class="project-year">2007</span> Translink</li>
              <li><span class="project-year">2007</span> Waterways Ireland</li>
              <li><span class="project-year">2002</span> MAGNI</li>
              <li><span class="project-year">2002</span> Tourism Ireland</li>
              <li><span class="project-year">2001</span> W5 Online</li>
              <li><span class="project-year">2000</span> Belfast Education Library Board</li>
              <li><span class="project-year">2000</span> Sports Council NI</li>
              <li><span class="project-year">1999</span> QUB</li>
            </ul>
          </div>
        </div>

        <!-- Column 3: Connect -->
        <div class="about-column">
          <h3 class="about-column__title">Connect</h3>
          <div class="about-column__content">
            <div class="about-social-links">
              <a href="https://www.linkedin.com/in/jonny-pyper-a31955123/" target="_blank" class="about-social-link">
                <i class="lni lni-linkedin"></i>
                <span>LinkedIn</span>
              </a>
              <a href="https://www.behance.net/jonnypyper" target="_blank" class="about-social-link">
                <i class="lni lni-behance"></i>
                <span>Behance</span>
              </a>
              <a href="https://github.com/crbntyp" target="_blank" class="about-social-link">
                <i class="lni lni-git"></i>
                <span>GitHub</span>
              </a>
              <a href="https://www.instagram.com/jonny_pyper/" target="_blank" class="about-social-link">
                <i class="lni lni-instagram"></i>
                <span>Instagram</span>
              </a>
            </div>

            <h3 class="about-column__title about-column__title--spaced">Bookmarks</h3>
            <div class="about-bookmarks">
              <?php foreach ($bookmarks as $bookmark): ?>
              <a href="<?php echo htmlspecialchars($bookmark['url']); ?>" target="_blank"><?php echo htmlspecialchars($bookmark['text']); ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
