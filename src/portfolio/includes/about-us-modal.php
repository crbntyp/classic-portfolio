<!-- About Us Modal -->
<div class="modal modal--fullscreen" id="aboutUsModal">
  <div class="modal__overlay modal__overlay--about">
    <button class="modal__close" id="aboutUsModalClose">
      <i class="lni lni-close"></i>
    </button>
    <div class="about-overlay-content">
      <h2 class="modal__logo-text">crbntyp</h2>

      <div class="about-grid">
        <!-- Column 1: About Me -->
        <div class="about-column">
          <h3 class="about-column__title">About</h3>
          <div class="about-column__content">
            <p>Engineering Manager with over 25 years of industry experience. Award-winning UI designer, UI engineer, and people leader with a passion for building products and people that make a difference.</p>
            <p>Currently leading multi-stack engineering teams, inspiring junior engineers, and building user experiences that delight.</p>
          </div>
        </div>

        <!-- Column 2: Projects -->
        <div class="about-column">
          <h3 class="about-column__title">Projects I've worked on</h3>
          <div class="about-column__content">
            <ul class="about-work-list">
              <li><span class="project-year project-year--present">2023 - PRESENT</span>Cloud: Agent, Antivirus, VR Forensics &amp; Orchestration Automation</li>
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
              <a href="/" class="about-social-link">
                <i class="lni lni-home"></i>
                <span>crbntyp Home</span>
              </a>
              <a href="https://www.linkedin.com/in/jonny-pyper-a31955123/" target="_blank" class="about-social-link">
                <i class="lni lni-linkedin"></i>
                <span>LinkedIn</span>
              </a>
              <a href="https://www.behance.net/jonnypyper" target="_blank" class="about-social-link">
                <i class="lni lni-behance"></i>
                <span>Behance</span>
              </a>
              <a href="<?php echo $pathPrefix; ?>portfolio.php" class="about-social-link">
                <i class="lni lni-gallery"></i>
                <span>Classic Portfolio</span>
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
          </div>
        </div>
      </div>

      <!-- Mobile navigation -->
      <div class="about-nav">
        <button class="about-nav__btn is-disabled" id="aboutNavPrev" onclick="aboutScrollLeft()">
          <i class="lni lni-arrow-left"></i>
        </button>
        <button class="about-nav__btn" id="aboutNavNext" onclick="aboutScrollRight()">
          <i class="lni lni-arrow-right"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function aboutScrollLeft() {
  var grid = document.querySelector('.about-grid');
  var prev = document.getElementById('aboutNavPrev');
  var next = document.getElementById('aboutNavNext');

  grid.scrollTo({ left: 0, behavior: 'smooth' });

  // Going to start - disable left, enable right
  prev.classList.add('is-disabled');
  next.classList.remove('is-disabled');
}

function aboutScrollRight() {
  var grid = document.querySelector('.about-grid');
  var prev = document.getElementById('aboutNavPrev');
  var next = document.getElementById('aboutNavNext');
  var col = grid.querySelector('.about-column');
  var width = col ? col.offsetWidth : 200;

  grid.scrollBy({ left: width, behavior: 'smooth' });

  // Moving right - enable left
  prev.classList.remove('is-disabled');

  // Check if at end after scroll
  setTimeout(function() {
    if (grid.scrollLeft >= grid.scrollWidth - grid.clientWidth - 10) {
      next.classList.add('is-disabled');
    }
  }, 500);
}

document.addEventListener('DOMContentLoaded', function() {
  var aboutContent = document.querySelector('.about-overlay-content');
  if (aboutContent) {
    aboutContent.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }
});
</script>
