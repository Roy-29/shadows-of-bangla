/**
 * SHADOWS OF BANGLA — Interactive Features
 * Random Discovery Summoner, Reading Progress Bar, and Mystery Folklore Quiz
 */

document.addEventListener('DOMContentLoaded', () => {
  initReadingProgressBar();
  initRandomPlaceSummoner();
  initMysteryQuiz();
});

/* ----------------------------------------------------
 * 1. Reading Progress Bar
 * ---------------------------------------------------- */
function initReadingProgressBar() {
  const progressBar = document.getElementById('reading-progress');
  if (!progressBar) return;

  window.addEventListener('scroll', () => {
    const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
    if (totalHeight <= 0) return;
    const progress = (window.scrollY / totalHeight) * 100;
    progressBar.style.width = Math.min(100, Math.max(0, progress)) + '%';
  }, { passive: true });
}

/* ----------------------------------------------------
 * 2. Random Place Discovery ("Summon the Unknown")
 * ---------------------------------------------------- */
function initRandomPlaceSummoner() {
  const summonBtns = document.querySelectorAll('.btn-summon-random');
  if (summonBtns.length === 0) return;

  summonBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();

      // Look for places array in window
      let places = window.SHADOWS_PLACES_CATALOG || [];

      // If not defined, fallback to fetch or link
      if (!places || places.length === 0) {
        window.location.href = 'explore.php?sort=random';
        return;
      }

      btn.disabled = true;
      const originalHtml = btn.innerHTML;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gazing into the Abyss...';

      // Subtle atmospheric flicker effect
      document.body.style.filter = 'invert(0.15) contrast(120%)';
      setTimeout(() => {
        document.body.style.filter = '';
        const randomIndex = Math.floor(Math.random() * places.length);
        const randomPlace = places[randomIndex];
        window.location.href = 'place.php?slug=' + encodeURIComponent(randomPlace.slug);
      }, 700);
    });
  });
}

/* ----------------------------------------------------
 * 3. Interactive Mystery & Folklore Quiz
 * ---------------------------------------------------- */
const QUIZ_QUESTIONS = [
  {
    question: "Which ancient fortified site in Bogura is the oldest identified urban centre in Bangladesh, dating back to 3rd century BCE?",
    options: ["Wari-Bateshwar", "Mahasthangarh", "Panam City", "Mainamati"],
    correct: 1,
    explanation: "Mahasthangarh (ancient Pundranagara) dates back to at least the Maurya period, confirmed by the 3rd century BCE Brahmi limestone inscription."
  },
  {
    question: "What mysterious atmospheric light phenomenon is famously reported over the marshes of the Sundarbans?",
    options: ["Aleya (Will-o'-the-Wisp)", "Bhoot-Jol", "Chhoba Fire", "Shurjo-Jala"],
    correct: 0,
    explanation: "Aleya lights are cold bioluminescent marsh lights caused by methane gas ignition, steeped in lore of perished fishermen's wandering souls."
  },
  {
    question: "How many historic brick merchant mansions stand along the single deserted avenue of Panam Nagar in Sonargaon?",
    options: ["24 mansions", "36 mansions", "52 mansions", "72 mansions"],
    correct: 2,
    explanation: "Panam Nagar is world-famous for its exactly 52 preserved mansions lining a 600-meter paved colonial boulevard."
  },
  {
    question: "Which famous 1920s legal trial involved a prince declared dead in Darjeeling who returned a decade later as a sadhu to reclaim his estate?",
    options: ["The Burdwan Dispute", "The Natore Raj Case", "The Bhawal Sannyasi Case", "The Puthia Succession"],
    correct: 2,
    explanation: "The Bhawal Sannyasi Case was resolved by the British Privy Council in 1946, affirming that the sadhu was indeed Prince Ramendra Narayan Roy."
  },
  {
    question: "According to northern folklore, why did Prince Ram willingly step into the dry excavation pit of Ramsagar lake in Dinajpur?",
    options: ["To find a golden crown", "To fulfill a water sacrifice dream to end famine", "To hide from imperial soldiers", "To rescue his lost sibling"],
    correct: 1,
    explanation: "Legend holds that King Ramnath was told only royal blood could open subterranean springs to quench his famine-stricken kingdom's thirst."
  }
];

function initMysteryQuiz() {
  const quizContainer = document.getElementById('interactive-quiz-container');
  if (!quizContainer) return;

  let currentQuestion = 0;
  let userScore = 0;
  let hasAnswered = false;

  function renderQuestion() {
    hasAnswered = false;
    const q = QUIZ_QUESTIONS[currentQuestion];

    quizContainer.innerHTML = `
      <div class="quiz-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.15em; color: var(--accent-red); font-weight: 700;">
            Question ${currentQuestion + 1} of ${QUIZ_QUESTIONS.length}
          </span>
          <span style="font-size: 0.8rem; color: var(--text-muted);">
            Score: <strong style="color: #fff;">${userScore}</strong>
          </span>
        </div>

        <h3 style="font-size: 1.25rem; font-family: var(--font-serif); margin-bottom: 1.25rem; line-height: 1.4;">
          ${q.question}
        </h3>

        <div class="quiz-options">
          ${q.options.map((opt, idx) => `
            <button class="quiz-option-btn" data-index="${idx}">
              <span style="display: inline-block; width: 22px; font-weight: 700; color: var(--accent-red);">${String.fromCharCode(65 + idx)}.</span>
              ${opt}
            </button>
          `).join('')}
        </div>

        <div id="quiz-feedback-box" style="display: none; margin-top: 1.25rem; padding: 1rem; border-radius: 6px; font-size: 0.9rem;"></div>

        <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
          <button id="quiz-next-btn" class="btn btn-primary" style="display: none;">
            ${currentQuestion === QUIZ_QUESTIONS.length - 1 ? 'View Final Judgment' : 'Next Enigma <i class="fa-solid fa-arrow-right"></i>'}
          </button>
        </div>
      </div>
    `;

    // Attach answer click listeners
    const optionBtns = quizContainer.querySelectorAll('.quiz-option-btn');
    const feedbackBox = document.getElementById('quiz-feedback-box');
    const nextBtn = document.getElementById('quiz-next-btn');

    optionBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        if (hasAnswered) return;
        hasAnswered = true;

        const selectedIndex = parseInt(btn.dataset.index, 10);
        const isCorrect = selectedIndex === q.correct;

        if (isCorrect) {
          userScore++;
          btn.classList.add('correct');
          feedbackBox.style.display = 'block';
          feedbackBox.style.background = 'rgba(16, 185, 129, 0.15)';
          feedbackBox.style.border = '1px solid #10b981';
          feedbackBox.style.color = '#34d399';
          feedbackBox.innerHTML = `<strong><i class="fa-solid fa-circle-check"></i> Correct!</strong> ${q.explanation}`;
        } else {
          btn.classList.add('incorrect');
          optionBtns[q.correct].classList.add('correct');
          feedbackBox.style.display = 'block';
          feedbackBox.style.background = 'rgba(239, 68, 68, 0.15)';
          feedbackBox.style.border = '1px solid #ef4444';
          feedbackBox.style.color = '#f87171';
          feedbackBox.innerHTML = `<strong><i class="fa-solid fa-circle-xmark"></i> Incorrect.</strong> ${q.explanation}`;
        }

        nextBtn.style.display = 'inline-flex';
      });
    });

    nextBtn.addEventListener('click', () => {
      currentQuestion++;
      if (currentQuestion < QUIZ_QUESTIONS.length) {
        renderQuestion();
      } else {
        renderResults();
      }
    });
  }

  function renderResults() {
    let rank = '';
    let badgeIcon = '';
    let description = '';

    if (userScore === 5) {
      rank = 'Grand Inquisitor of Bengal';
      badgeIcon = 'fa-crown';
      description = 'Uncanny mastery! You have pierced through centuries of fog and separated documented history from regional myth with supreme precision.';
    } else if (userScore >= 3) {
      rank = 'Shadow Archivist';
      badgeIcon = 'fa-book-skull';
      description = 'Commendable! Your knowledge of Bangladesh’s ancient forts, abandoned mansions, and delta folklore runs deep.';
    } else {
      rank = 'Novice Wanderer';
      badgeIcon = 'fa-compass';
      description = 'You have touched the threshold of the unknown. Explore the archive more closely to unravel the deeper secrets of Bangla.';
    }

    quizContainer.innerHTML = `
      <div class="quiz-box" style="text-align: center; padding: 3rem 2rem;">
        <div style="width: 70px; height: 70px; margin: 0 auto 1.5rem; border-radius: 50%; background: rgba(158, 27, 50, 0.2); border: 2px solid var(--accent-red); display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--accent-red); box-shadow: 0 0 25px var(--accent-glow);">
          <i class="fa-solid ${badgeIcon}"></i>
        </div>

        <span style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; color: var(--accent-red); font-weight: 700;">Final Judgment</span>
        <h3 style="font-size: 2rem; font-family: var(--font-serif); margin: 0.5rem 0 0.75rem; color: #fff;">
          ${rank}
        </h3>

        <p style="font-size: 1.15rem; color: var(--fog-grey); margin-bottom: 1.5rem;">
          You solved <strong>${userScore} out of ${QUIZ_QUESTIONS.length}</strong> enigmas correctly.
        </p>

        <p style="max-width: 500px; margin: 0 auto 2rem; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6;">
          ${description}
        </p>

        <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
          <button id="quiz-retry-btn" class="btn btn-outline-red">
            <i class="fa-solid fa-rotate-right"></i> Challenge Again
          </button>
          <a href="explore.php" class="btn btn-primary">
            Explore Locations <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
    `;

    document.getElementById('quiz-retry-btn')?.addEventListener('click', () => {
      currentQuestion = 0;
      userScore = 0;
      renderQuestion();
    });
  }

  renderQuestion();
}

