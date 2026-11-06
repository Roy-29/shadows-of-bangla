/**
 * SHADOWS OF BANGLA — Interactive Local Development & Preview Server
 * Built with native Node.js (Zero npm dependencies required)
 * Allows instant viewing and testing in Google Chrome out-of-the-box!
 */

const http = require('http');
const fs = require('fs');
const path = require('path');
const url = require('url');

const PORT = 8000;
const ROOT_DIR = __dirname;

// MIME types dictionary
const MIME_TYPES = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'application/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.svg': 'image/svg+xml',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.webp': 'image/webp',
  '.ico': 'image/x-icon'
};

// In-Memory Data Store (Mirrors MySQL shadows_of_bangla database)
const categories = [
  { id: 1, name: 'Haunted Legends', slug: 'haunted-legends', icon: 'fa-ghost', description: 'Sites marked by tragic folklore, oral ghost tales, and longstanding spectral beliefs.' },
  { id: 2, name: 'Abandoned Places', slug: 'abandoned-places', icon: 'fa-house-crack', description: 'Deserted townships, crumbling zamindar mansions, and forgotten settlements left to nature.' },
  { id: 3, name: 'Historical Mysteries', slug: 'historical-mysteries', icon: 'fa-landmark', description: 'Ancient civilizations, lost forts, enigmatic trade ports, and archaeological puzzles.' },
  { id: 4, name: 'Ghost Stories', slug: 'ghost-stories', icon: 'fa-skull', description: 'Eerie accounts passed down generations across riverbanks, marshes, and rural hamlets.' },
  { id: 5, name: 'Mysterious Buildings', slug: 'mysterious-buildings', icon: 'fa-monument', description: 'Architectural enigmas, forgotten clocktowers, hidden tunnels, and labyrinthine chambers.' },
  { id: 6, name: 'Local Folklore', slug: 'local-folklore', icon: 'fa-book-skull', description: 'River deities, forest spirits, shape-shifters, and village folk traditions.' }
];

let places = [
  {
    id: 1,
    name: 'Panam City (Panam Nagar)',
    slug: 'panam-city-sonargaon',
    summary: 'A silent 19th-century merchant township of 52 colonnaded brick mansions, abruptly abandoned after historic conflicts.',
    description: 'Panam City in Sonargaon stands as one of the most hauntingly preserved abandoned settlements in South Asia. A single 600-meter paved avenue is flanked by 52 derelict brick mansions blending Mughal, Classical Greek, and British colonial architecture. Walking through its overgrown lanes at twilight feels like stepping into a frozen, mournful past.',
    division: 'Dhaka',
    district: 'Narayanganj',
    address: 'Panam Nagar, Sonargaon, Narayanganj 1440',
    latitude: 23.6493,
    longitude: 90.6017,
    cover_image: 'assets/images/panam-city.svg',
    historical_background: 'Sonargaon was the historic medieval capital of Bengal under Isa Khan. During the late 19th and early 20th centuries, wealthy Hindu textile merchants (Tanti community) constructed Panam Nagar as a protected enclave. Following communal riots and the 1965 war, the merchants fled suddenly, leaving their sprawling estates and vaults behind.',
    local_legends: 'Local villagers speak of nocturnal echoes along the central avenue—the sound of carriage wheels rolling on brickwork, faint clinking of gold coins, and hurried footsteps on empty verandahs. Traditional elders believe the spirits of merchants returned to safeguard their hidden treasures.',
    paranormal_claims: 'Night guards report cold drafts near Building No. 24, sudden drops in ambient temperature, and the faint scent of sandalwood burning in unoccupied rooms.',
    why_mysterious: 'The eerie completeness of the town: 52 intact yet rotting mansions along one silent corridor, surrounded by water canals, completely deserted by their original builders.',
    category_id: 2,
    category_name: 'Abandoned Places',
    category_slug: 'abandoned-places',
    category_icon: 'fa-house-crack',
    verification_status: 'VERIFIED HISTORY',
    sources: 'Department of Archaeology Bangladesh; Banglapedia - Sonargaon; World Monuments Fund Watch List.',
    is_featured: 1,
    views_count: 1420
  },
  {
    id: 2,
    name: 'Lalbagh Fort & The Unfinished Maze',
    slug: 'lalbagh-fort-dhaka',
    summary: 'A 17th-century Mughal river fortress shrouding an unfinished tomb and subterranean secret tunnels beneath Old Dhaka.',
    description: 'Lalbagh Fort looms over the historic Buriganga riverbank. Commenced in 1678 by Prince Muhammad Azam, third son of Emperor Aurangzeb, its construction was halted forever following the tragic death of his beloved betrothed, Iran Dukht (Pari Bibi). Beneath its imposing bastions lies a notorious labyrinth of sealed subterranean passages.',
    division: 'Dhaka',
    district: 'Dhaka',
    address: 'Lalbagh Road, Old Dhaka 1211',
    latitude: 23.7188,
    longitude: 90.3882,
    cover_image: 'assets/images/lalbagh-fort.svg',
    historical_background: 'Construction began under Prince Muhammad Azam and continued under Shaista Khan. When Shaista Khan\'s daughter Pari Bibi died suddenly inside the fort, it was deemed cursed and abandoned. Secret tunnels connecting the fort to the Buriganga river and Zinzira Palace were carved for military escape.',
    local_legends: 'During colonial times, British sepoys and hunting hounds sent to explore the southern tunnels reportedly never returned, prompting British military engineers to seal the passages with brickwork.',
    paranormal_claims: 'Old Dhaka residents recount a spectral maiden in Mughal silk wandering the southern parapet on full moon nights, gazing toward lost riverbeds.',
    why_mysterious: 'The sealed underground tunnel network whose exact depth and endpoints remain unmapped, coupled with the abrupt superstitious cessation of imperial construction.',
    category_id: 1,
    category_name: 'Haunted Legends',
    category_slug: 'haunted-legends',
    category_icon: 'fa-ghost',
    verification_status: 'VERIFIED HISTORY',
    sources: 'Syed Muhammed Taifoor, Forgotten Dhaka (1952); National Museum of Bangladesh Archives.',
    is_featured: 1,
    views_count: 2150
  },
  {
    id: 3,
    name: 'Mahasthangarh & Parshuram\'s Palace',
    slug: 'mahasthangarh-ancient-pundranagara',
    summary: 'The 2,500-year-old fortified ruins of Pundranagara, bearing legends of the fish-riding saint and the Well of Life.',
    description: 'Mahasthangarh in Bogura is the earliest urban archaeological site identified in Bangladesh, dating back to at least the 3rd century BCE (Maurya Period). Vast red brick ramparts overlook the sacred Karatoya River, concealing millennia of Buddhist, Hindu, and Muslim civilizations.',
    division: 'Rajshahi',
    district: 'Bogura',
    address: 'Shibganj Upazila, Bogura 5800',
    latitude: 24.9608,
    longitude: 89.3456,
    cover_image: 'assets/images/mahasthangarh.svg',
    historical_background: 'Identified with ancient Pundranagara from a Brahmi limestone inscription found in 1931. The fortified city served as a provincial capital under the Mauryas, Guptas, Pala Empire, and Sena dynasty before falling in the 13th century.',
    local_legends: 'Folklore states that King Parshuram possessed the Jiat Kunda (Well of Life), whose water revived fallen soldiers until Sultan Balkhi Mahisawar (the Saint on a Fish) neutralized its properties.',
    paranormal_claims: 'Local farmers claim the soil around Gobinda Bhita exudes an unexplained magnetic chill at night, and travelers report hearing clashing blades when fog envelopes the riverbed.',
    why_mysterious: 'Centuries of unexcavated ruins buried dozens of feet underground, and the oral memory of an ancient city that survived for two millennia before vanishing into mound ruins.',
    category_id: 3,
    category_name: 'Historical Mysteries',
    category_slug: 'historical-mysteries',
    category_icon: 'fa-landmark',
    verification_status: 'VERIFIED HISTORY',
    sources: 'Brahmi Inscription of Mahasthan (Epigraphia Indica); Ahmed, Nazimuddin, Mahasthan (1975).',
    is_featured: 1,
    views_count: 980
  },
  {
    id: 4,
    name: 'Puthia Rajbari Complex & Sunken Shrines',
    slug: 'puthia-rajbari-complex',
    summary: 'A sprawling 19th-century royal palace surrounded by fourteen ornate terracotta temples and midnight sitar lore.',
    description: 'Puthia Rajbari in Rajshahi features one of the most stunning clusters of historic Hindu terracotta temples in South Asia. Constructed by Queen Hemanta Kumari Devi in 1895, the estate is renowned for its Corinthian columns and sacred freshwater dighis.',
    division: 'Rajshahi',
    district: 'Rajshahi',
    address: 'Puthia Upazila, Rajshahi 6260',
    latitude: 24.3644,
    longitude: 88.8358,
    cover_image: 'assets/images/puthia-palace.svg',
    historical_background: 'Founded in the late 16th century by Nilambar. The present palace was rebuilt after the 1897 earthquake and features the Pancha Ratna Govinda Temple and the grand Shiva Temple.',
    local_legends: 'Local tradition claims submerged staircases connect the Shiva Temple to the bed of the Shiv Sagar lake. Sitar melodies are said to echo from the upper balcony late at night.',
    paranormal_claims: 'Night watchmen report hearing sitar music and footsteps on hollow wooden floors when gates are barred.',
    why_mysterious: 'Submerged temple vaults beneath lakes that have never been drained or sonar-mapped.',
    category_id: 5,
    category_name: 'Mysterious Buildings',
    category_slug: 'mysterious-buildings',
    category_icon: 'fa-monument',
    verification_status: 'LOCAL FOLKLORE',
    sources: 'Hasan, Perween, Sultans and Mosques; District Gazetteers of Rajshahi.',
    is_featured: 0,
    views_count: 750
  },
  {
    id: 5,
    name: 'Wari-Bateshwar & Submerged Metropolis',
    slug: 'wari-bateshwar-ancient-port',
    summary: 'A 2,500-year-old forgotten fortified river metropolis trading with ancient Rome and Southeast Asia.',
    description: 'Hidden beneath quiet villages in Narsingdi lies an immense fortified urban centre dating back to 450 BCE, proving ancient Bengal had deep trans-oceanic trade ties.',
    division: 'Dhaka',
    district: 'Narsingdi',
    address: 'Belabo Upazila, Narsingdi 1640',
    latitude: 24.0933,
    longitude: 90.8350,
    cover_image: 'assets/images/wari-bateshwar.svg',
    historical_background: 'Uncovered pit-dwellings, rouletted pottery, Northern Black Polished Ware, and over 4,000 punch-marked silver coins linked with Ptolemy\'s Sounagoura.',
    local_legends: 'Villagers warned for centuries against disturbing Asur Dighi (The Giant\'s Moat) after sundown.',
    paranormal_claims: 'Electrostatic disruptions reported during overcast weather near ancient mud ramparts.',
    why_mysterious: 'A vast international trading city that flourished for centuries and vanished completely from dynastic records.',
    category_id: 3,
    category_name: 'Historical Mysteries',
    category_slug: 'historical-mysteries',
    category_icon: 'fa-landmark',
    verification_status: 'VERIFIED HISTORY',
    sources: 'Prof. Sufi Mustafizur Rahman et al., Wari-Bateshwar: Archaeological Heritage (2009).',
    is_featured: 0,
    views_count: 620
  },
  {
    id: 6,
    name: 'Bhawal Rajbari & Sannyasi Court',
    slug: 'bhawal-rajbari-sannyasi-court',
    summary: 'The royal court of Gazipur, centre of the 20th century\'s greatest royal resurrection mystery.',
    description: 'In 1909, Prince Ramendra Narayan Roy died and was cremated in Darjeeling. Twelve years later, an ash-smeared sadhu returned to Dhaka claiming to be the prince, winning the trial of the century in London\'s Privy Council.',
    division: 'Dhaka',
    district: 'Gazipur',
    address: 'Joydebpur Sadar, Gazipur 1700',
    latitude: 24.0016,
    longitude: 90.4285,
    cover_image: 'assets/images/bhawal-palace.svg',
    historical_background: 'The Bhawal Case lasted over two decades. The Privy Council delivered its final verdict in 1946 confirming the sadhu was indeed the resurrected prince.',
    local_legends: 'The prince reportedly escaped his cremation when a sudden hailstorm scattered the pyre and Naga sadhus revived his comatose body.',
    paranormal_claims: 'Midnight gavel strikes and shuffling papers are said to echo in the eastern council hall.',
    why_mysterious: 'A legally verified case of a man rising from his death pyre to reclaim his royal estate.',
    category_id: 3,
    category_name: 'Historical Mysteries',
    category_slug: 'historical-mysteries',
    category_icon: 'fa-landmark',
    verification_status: 'VERIFIED HISTORY',
    sources: 'Chatterjee, Partha, A Princely Impostor? (2002); Privy Council Appeal No. 42 of 1943.',
    is_featured: 0,
    views_count: 890
  },
  {
    id: 7,
    name: 'Foy\'s Lake & Batali Hill',
    slug: 'foys-lake-batali-hill-chattogram',
    summary: 'A century-old man-made mountain reservoir and the highest hill of Chattogram, home to the Lady in White legend.',
    description: 'Foy\'s Lake was engineered in 1924 by Assam-Bengal Railway. Overlooking the water is Batali Hill with WW2 British anti-aircraft bunkers and dense hill tracks.',
    division: 'Chattogram',
    district: 'Chattogram',
    address: 'Pahartali - Khulshi, Chattogram 4202',
    latitude: 22.3667,
    longitude: 91.8028,
    cover_image: 'assets/images/foys-lake.svg',
    historical_background: 'Engineered by Mr. Foy in 1924 for railway steam engines. Batali Hill held British RAF anti-aircraft posts during World War II.',
    local_legends: 'The "Lady in White" (Bada Meye) is said to wander the misty slopes above the reservoir at dusk.',
    paranormal_claims: 'Compass drift and sudden disorientation near the overgrown military artillery trenches.',
    why_mysterious: 'Secluded hilly topography, WW2 bunkers, and unexplained drownings across the deep reservoir.',
    category_id: 1,
    category_name: 'Haunted Legends',
    category_slug: 'haunted-legends',
    category_icon: 'fa-ghost',
    verification_status: 'LOCAL FOLKLORE',
    sources: 'Eastern Bengal and Assam District Gazetteers: Chittagong (1908).',
    is_featured: 0,
    views_count: 1100
  },
  {
    id: 8,
    name: 'Jaintia Megalithic Monuments',
    slug: 'jaintia-megalithic-monuments-sylhet',
    summary: 'Ancient sacrificial stone slabs, dolmens, and menhirs of the matrilineal Khasi-Jaintia Kingdom.',
    description: 'At the base of the Meghalaya hill ridges in Sylhet lie gigantic sandstone dolmens and menhirs marking royal assemblies of the sovereign Jaintia Kingdom.',
    division: 'Sylhet',
    district: 'Sylhet',
    address: 'Jaintiapur Upazila, Sylhet 3150',
    latitude: 25.1328,
    longitude: 92.1283,
    cover_image: 'assets/images/jaintia-stones.svg',
    historical_background: 'Erected between the 15th and 18th centuries following ancient Khasi megalithic traditions to honor royal ancestors and commemorate military victories.',
    local_legends: 'The sacrificial throne stone was believed to test truth: anyone swearing a false oath while touching it was struck by mountain lightning.',
    paranormal_claims: 'During monsoon storms, the megaliths are said to emit a low vibrating resonance that panics livestock.',
    why_mysterious: 'The prehistoric engineering required to transport multi-ton sandstone boulders across rugged river valleys.',
    category_id: 6,
    category_name: 'Local Folklore',
    category_slug: 'local-folklore',
    category_icon: 'fa-book-skull',
    verification_status: 'VERIFIED HISTORY',
    sources: 'Gait, Edward, A History of Assam (1906); Gurdon, The Khasis (1914).',
    is_featured: 0,
    views_count: 540
  },
  {
    id: 9,
    name: 'Ancient Wells of Kuakata',
    slug: 'kuakata-ancient-rakhine-wells',
    summary: 'Deep coastal freshwater wells dug by 18th-century refugees on the edge of the turbulent Bay of Bengal.',
    description: 'Behind Kuakata\'s sandy beaches lie wooden-framed freshwater wells excavated by Rakhine refugees fleeing Burmese conquest in 1784.',
    division: 'Barishal',
    district: 'Patuakhali',
    address: 'Kalapara, Patuakhali 8650',
    latitude: 21.8174,
    longitude: 90.1189,
    cover_image: 'assets/images/kuakata-well.svg',
    historical_background: 'Following King Bodawpaya\'s conquest of Arakan in 1784, Rakhine refugees built deep freshwater wells protected by massive woven timber rings.',
    local_legends: 'Fishermen speak of the Ghost Well where ancient chanting rises above the roar of the surf during stormy new moons.',
    paranormal_claims: 'Phantom lanterns hovering over the wave line that vanish instantly when boats approach.',
    why_mysterious: 'An ancient community whose survival depended on freshwater carved out of an unforgiving salt desert.',
    category_id: 6,
    category_name: 'Local Folklore',
    category_slug: 'local-folklore',
    category_icon: 'fa-book-skull',
    verification_status: 'LOCAL FOLKLORE',
    sources: 'Banglapedia - Rakhines in Bangladesh; Patuakhali District Gazetteer.',
    is_featured: 0,
    views_count: 670
  },
  {
    id: 10,
    name: 'Ramsagar Dighi & Royal Sacrifice',
    slug: 'ramsagar-dighi-dinajpur',
    summary: 'The largest man-made reservoir in Bangladesh, built during famine and haunted by a prince\'s water sacrifice.',
    description: 'Spanning over 437,000 square meters in Dinajpur, Ramsagar was excavated between 1750 and 1755 by Raja Ramnath to save his subjects from catastrophic drought.',
    division: 'Rangpur',
    district: 'Dinajpur',
    address: 'Tejpur, Dinajpur Sadar 5200',
    latitude: 25.5539,
    longitude: 88.6292,
    cover_image: 'assets/images/ramsagar.svg',
    historical_background: 'Excavated during the great famine of 1750–1755 to provide food wages to thousands of peasants, measuring over 1,000 meters in length.',
    local_legends: 'When the excavation remained bone-dry, the Raja sacrificed his son Prince Ram, after which subterranean springs miraculously surged from the depths.',
    paranormal_claims: 'Visitors walking the southern ghats at twilight report seeing the shimmering silhouette of a youth just beneath the water surface.',
    why_mysterious: 'The eerie silence of the northern plains and an unbroken oral tradition passed down for 270 years.',
    category_id: 1,
    category_name: 'Haunted Legends',
    category_slug: 'haunted-legends',
    category_icon: 'fa-ghost',
    verification_status: 'LOCAL FOLKLORE',
    sources: 'Montgomery Martin, Eastern India (1838); Dinajpur District Gazetteer.',
    is_featured: 0,
    views_count: 830
  },
  {
    id: 11,
    name: 'Sundarbans Aleya & Bonbibi Lore',
    slug: 'sundarbans-aleya-ghost-lights',
    summary: 'The deepest mangrove wilderness on Earth, where mysterious ghost lights and forest deities guard the tiger realm.',
    description: 'The Sundarbans delta is the sanctuary of the Royal Bengal Tiger and home to mysterious Aleya marsh lights and Bonbibi folklore.',
    division: 'Khulna',
    district: 'Bagerhat',
    address: 'Sharankhola, Bagerhat 9320',
    latitude: 21.8544,
    longitude: 89.7828,
    cover_image: 'assets/images/sundarbans.svg',
    historical_background: 'Ancient temple ruins found in the lower delta prove settlement attempts dating back to the Chandraketugarh and Sena dynasties.',
    local_legends: 'Folk traditions center around demon king Dakshin Rai and Aleya ghost lights hovering over marshes that lure boatmen into quicksand.',
    paranormal_claims: 'Cold flame flares from methane combustion hovering inches above tidal waterways drifting against the wind.',
    why_mysterious: 'Primal mangrove frontiers where bioluminescent nature and ancient fear blur reality.',
    category_id: 6,
    category_name: 'Local Folklore',
    category_slug: 'local-folklore',
    category_icon: 'fa-book-skull',
    verification_status: 'LOCAL FOLKLORE',
    sources: 'Eaton, Richard M., The Rise of Islam and the Bengal Frontier (1993).',
    is_featured: 1,
    views_count: 1890
  },
  {
    id: 12,
    name: 'Baliati Palace & The Inner Sanctum',
    slug: 'baliati-palace-manikganj',
    summary: 'A colossal 19th-century zamindar estate of 200 rooms, towering Greco-Roman pillars, and hidden family courtyards.',
    description: 'Spanning over 5.88 acres in Manikganj, Baliati Palace comprises seven grand detached mansions and soaring Greco-Roman colonnades.',
    division: 'Dhaka',
    district: 'Manikganj',
    address: 'Saturia Upazila, Manikganj 1810',
    latitude: 23.9961,
    longitude: 90.0406,
    cover_image: 'assets/images/baliati-palace.svg',
    historical_background: 'Founded by salt merchant Gobinda Ram Saha in the late 18th century and expanded by four rival family lineages.',
    local_legends: 'Village elders speak of silver bullion sealed beneath the northern pond and ankle-bells heard pacing the padlocked inner courtyards.',
    paranormal_claims: 'Sounds of rolling marble balls on upper wooden floorings in chambers that have been locked for decades.',
    why_mysterious: 'Monumental European pillars rising out of rural paddy fields, standing vast, silent, and empty.',
    category_id: 5,
    category_name: 'Mysterious Buildings',
    category_slug: 'mysterious-buildings',
    category_icon: 'fa-monument',
    verification_status: 'VERIFIED HISTORY',
    sources: 'Department of Archaeology Bangladesh; Hunter, W. W., Statistical Account of Bengal.',
    is_featured: 0,
    views_count: 910
  },
  {
    id: 13,
    name: 'Uttara Ganabhaban (Dighapatia Palace)',
    slug: 'uttara-ganabhaban-dighapatia-palace',
    summary: 'The historic fortified palace of Natore, famous for its grand clock tower, defensive moat, and Italian marble statues.',
    description: 'Surrounded by a wide defensive moat and high ramparts in Natore, Uttara Ganabhaban features an imposing British mechanical clock tower and marble statues.',
    division: 'Rajshahi',
    district: 'Natore',
    address: 'Natore Sadar, Natore 6400',
    latitude: 24.4339,
    longitude: 89.0064,
    cover_image: 'assets/images/uttara-ganabhaban.svg',
    historical_background: 'Built in 1734 by Dayaram Roy, dewan of Rani Bhabani, and rebuilt in 1897 by Raja Pramada Nath Roy.',
    local_legends: 'The grand mechanical clock chimed mournfully on days when royal family members fell ill miles away.',
    paranormal_claims: 'Night guards report seeing shadows crossing the moat bridge and hearing muffled footsteps in the royal reception hall.',
    why_mysterious: 'The juxtaposition of sovereign power, defensive moats, and Victorian clockwork isolated in the quiet northern plains.',
    category_id: 5,
    category_name: 'Mysterious Buildings',
    category_slug: 'mysterious-buildings',
    category_icon: 'fa-monument',
    verification_status: 'VERIFIED HISTORY',
    sources: 'District Gazetteer of Rajshahi and Natore; Bangladesh Tourism Board.',
    is_featured: 0,
    views_count: 720
  },
  {
    id: 14,
    name: 'Choto Sona Mosque & Gaur Border Ruins',
    slug: 'choto-sona-mosque-gaur-ruins',
    summary: 'The forgotten borderland ruins of Gaur, once one of medieval Asia\'s greatest imperial capitals.',
    description: 'A 15-domed jewel of Bengal Sultanate architecture built during Sultan Alauddin Husain Shah\'s reign (1493–1519), surrounded by the overgrown ramparts of Gaur.',
    division: 'Rajshahi',
    district: 'Chapai Nawabganj',
    address: 'Shibganj, Chapai Nawabganj 6340',
    latitude: 24.8014,
    longitude: 88.1478,
    cover_image: 'assets/images/choto-sona.svg',
    historical_background: 'Gaur was the glorious imperial capital of the Bengal Sultanate until an apocalyptic plague in 1575 wiped out the population.',
    local_legends: 'Border villagers share tales of spectral cavalry patrols riding past the Kotwali Darwaza during pitch-dark monsoon squalls.',
    paranormal_claims: 'Feelings of intense sorrow and temporal disorientation along the overgrown ancient moats.',
    why_mysterious: 'The sudden demise of a metropolis of hundreds of thousands in a single season, leaving behind golden domes swallowed by jungle.',
    category_id: 3,
    category_name: 'Historical Mysteries',
    category_slug: 'historical-mysteries',
    category_icon: 'fa-landmark',
    verification_status: 'VERIFIED HISTORY',
    sources: 'Creighton, Henry, The Ruins of Gaur (1817); Dani, Muslim Architecture in Bengal.',
    is_featured: 0,
    views_count: 860
  }
];

let stories = [
  {
    id: 1,
    title: 'The Vanishing Merchants of Panam Nagar: 52 Mansions in Mourning',
    slug: 'the-vanishing-merchants-of-panam-nagar',
    excerpt: 'How an entire class of wealthy cloth merchants packed their gold, sealed their brick vaults, and vanished overnight from Sonargaon.',
    content: `<p>On a humid dusk in Sonargaon, the light catches the cracked terracotta moldings of Panam City at an angle that reveals centuries of sorrow. Fifty-two brick mansions stand shoulder to shoulder along a single 600-meter paved road. Once, this corridor buzzed with the brisk trade of Bengal's legendary Muslin and fine English textiles.</p>
    <p>Today, there is only the rustle of banyan roots splitting brickwork. The windows, once adorned with painted timber shutters, look onto the street like hollow eye sockets.</p>
    <h3>The Golden Age of Panam</h3>
    <p>Panam Nagar was constructed in the late 19th and early 20th centuries as an affluent merchant enclave. Each residence was conceived as both home and secure vault with high ceilings and inner open-air courtyards.</p>
    <h3>The Night of Flight</h3>
    <p>Following communal tensions and the 1965 India-Pakistan war, families who lived in Sonargaon for generations departed under cover of darkness, dropping gold into deep wells in hopes of returning.</p>
    <h3>Echoes on the Cobblestones</h3>
    <p>Visitors who linger after tourist gates close speak of 'Hawa-e-Awaz'—a whispering sound mimicking the distant hum of a crowded market.</p>`,
    category_id: 2,
    category_name: 'Abandoned Places',
    category_slug: 'abandoned-places',
    cover_image: 'assets/images/panam-city.svg',
    author: 'Tariqur Rahman, Heritage Researcher',
    reading_time: 6,
    is_featured: 1,
    created_at: '2026-11-01'
  },
  {
    id: 2,
    title: 'The Subterranean Labyrinth of Lalbagh: Where British Troops Vanished',
    slug: 'the-subterranean-labyrinth-of-lalbagh',
    excerpt: 'Investigating the infamous southern tunnels beneath Old Dhaka and why imperial British officers ordered them sealed.',
    content: `<p>Old Dhaka is a city built upon layers. Beneath the chaos of chawk and the narrow alleys of Lalbagh lies Fort Aurangabad. Beneath its imposing bastions lies a notorious labyrinth of sealed subterranean passages.</p>
    <h3>The Escape Arteries of the Subahdar</h3>
    <p>Mughal military engineers carved subterranean conduits designed to allow covert evacuation under the Buriganga riverbed to Zinzira Palace.</p>
    <h3>The Colonial Legend</h3>
    <p>In the 19th century, British expeditions sent sepoy soldiers and hunting hounds into the southern tunnel maze. Reports hold they never returned, prompting engineers to wall over the entrances permanently.</p>`,
    category_id: 1,
    category_name: 'Haunted Legends',
    category_slug: 'haunted-legends',
    cover_image: 'assets/images/lalbagh-fort.svg',
    author: 'Farhana Chowdhury, Historic Archivist',
    reading_time: 5,
    is_featured: 1,
    created_at: '2026-11-02'
  },
  {
    id: 3,
    title: 'The Sannyasi Prince of Bhawal: Dead in Darjeeling, Alive in Dhaka',
    slug: 'the-sannyasi-prince-of-bhawal-mystery',
    excerpt: 'The astonishing legal battle of a Bengali prince who was cremated in the Himalayas only to return a decade later.',
    content: `<p>In May 1909 in Darjeeling, Prince Ramendra Narayan Roy was pronounced dead. Twelve years later, an ash-smeared sannyasi appeared at Buckland Bund in Dhaka, claiming to be the prince.</p>
    <h3>The Imperial Trial of the Century</h3>
    <p>Over hundreds of court days, medical experts compared 24 distinct bodily marks. In 1946, the Privy Council in London confirmed the sadhu was indeed the prince.</p>`,
    category_id: 3,
    category_name: 'Historical Mysteries',
    category_slug: 'historical-mysteries',
    cover_image: 'assets/images/bhawal-palace.svg',
    author: 'S. M. Ashraful Alam',
    reading_time: 7,
    is_featured: 0,
    created_at: '2026-11-03'
  },
  {
    id: 4,
    title: 'Voices in the Mangrove: Aleya Lights and Bonbibi\'s Protection',
    slug: 'voices-in-the-mangrove-aleya-lights-bonbibi',
    excerpt: 'Into the tidal waterways of the Sundarbans, where fishermen navigate ghost lights and ancient forest deities.',
    content: `<p>In the world's largest mangrove forest, woodcutters and honey-hunters pray before idols of Bonbibi before entering the wilderness where Dakshin Rai hunts.</p>
    <h3>The Phantoms of the Marshes</h3>
    <p>Aleya lights hover above the waterways. Scientifically combustion of marsh gas; to local boatmen, they are souls seeking company in eternity.</p>`,
    category_id: 6,
    category_name: 'Local Folklore',
    category_slug: 'local-folklore',
    cover_image: 'assets/images/sundarbans.svg',
    author: 'Kazi Mehedi Hasan',
    reading_time: 5,
    is_featured: 0,
    created_at: '2026-11-04'
  },
  {
    id: 5,
    title: 'The Sacrificial Waters of Ramsagar: A Prince\'s Tragic Thirst',
    slug: 'the-sacrificial-waters-of-ramsagar-prince',
    excerpt: 'Why a 270-year-old artificial lake in Dinajpur carries an unbroken oral memory of a royal sacrifice.',
    content: `<p>Between 1750 and 1755, Raja Ramnath of Dinajpur dug a massive 1km lake during famine. When the dry clay yielded no water, Prince Ram stepped into the pit as a sacrifice, and waters surged to quench the kingdom.</p>`,
    category_id: 1,
    category_name: 'Haunted Legends',
    category_slug: 'haunted-legends',
    cover_image: 'assets/images/ramsagar.svg',
    author: 'Mahmudul Karim',
    reading_time: 4,
    is_featured: 0,
    created_at: '2026-11-05'
  }
];

let submissions = [
  { id: 1, submitter_name: 'Nayeem Islam', email: 'nayeem@gmail.com', story_title: 'The Spectral Bell of Batali Hill', district: 'Chattogram', location_name: 'Batali Hill WW2 Bunkers', category_name: 'Haunted Legends', story_content: 'During my trekking trip to Batali Hill last winter, we distinctly heard a heavy brass ship bell ringing rhythmically three times...', status: 'pending', created_at: '2026-11-01' },
  { id: 2, submitter_name: 'Shaila Afroze', email: 'shaila@yahoo.com', story_title: 'Whispers at Baliati Inner Court', district: 'Manikganj', location_name: 'Baliati Palace Andar Mahal', category_name: 'Mysterious Buildings', story_content: 'My uncle was a caretaker at Baliati Palace. One evening he saw light flickering in the padlocked chamber...', status: 'pending', created_at: '2026-11-03' }
];

// Reusable Layout Generator
function renderPage(title, activePage, bodyContent) {
  const mapPlacesJson = JSON.stringify(places.map(p => ({
    id: p.id,
    name: p.name,
    slug: p.slug,
    summary: p.summary,
    division: p.division,
    district: p.district,
    latitude: p.latitude,
    longitude: p.longitude,
    cover_image: p.cover_image,
    category_slug: p.category_slug
  })));

  return `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${title} — SHADOWS OF BANGLA</title>
    <meta name="description" content="Explore mysterious places, abandoned ruins, folk legends, and historical enigmas across Bangladesh.">
    
    <!-- Preconnect Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Leaflet.js CSS for Interactive Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Site Styles -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div id="reading-progress" class="reading-progress-bar"></div>
    <div class="ambient-fog-layer"></div>
    <canvas id="particle-canvas"></canvas>

    <!-- Navigation Header -->
    <header class="site-navbar">
        <div class="container nav-wrapper">
            <a href="/" class="brand-logo">
                <div class="logo-symbol"><i class="fa-solid fa-ghost"></i></div>
                <div class="brand-text">
                    <span class="brand-name">SHADOWS OF BANGLA</span>
                    <span class="brand-tagline-nav">Beyond the Known.</span>
                </div>
            </a>

            <nav>
                <ul class="nav-menu">
                    <li><a href="/" class="nav-link ${activePage === 'home' ? 'active' : ''}">Home</a></li>
                    <li><a href="/explore.php" class="nav-link ${activePage === 'explore' ? 'active' : ''}">Explore Places</a></li>
                    <li><a href="/map.php" class="nav-link ${activePage === 'map' ? 'active' : ''}">Interactive Map</a></li>
                    <li><a href="/stories.php" class="nav-link ${activePage === 'stories' ? 'active' : ''}">Stories</a></li>
                    <li><a href="/submit-story.php" class="nav-link ${activePage === 'submit' ? 'active' : ''}">Submit Story</a></li>
                    <li><a href="/about.php" class="nav-link ${activePage === 'about' ? 'active' : ''}">About</a></li>
                    <li><a href="/admin/index.php" class="nav-link text-red"><i class="fa-solid fa-shield-halved"></i> Admin</a></li>
                </ul>
            </nav>

            <div class="nav-actions">
                <div style="position: relative;">
                    <div id="ambient-sound-toggle" class="audio-control-pill" title="Toggle Atmospheric Soundscape">
                        <div class="equalizer-bars">
                            <span class="equalizer-bar"></span>
                            <span class="equalizer-bar"></span>
                            <span class="equalizer-bar"></span>
                        </div>
                        <span class="pill-text">Atmosphere OFF</span>
                        <i class="fa-solid fa-volume-high" style="font-size: 0.7rem; margin-left: 2px;"></i>
                    </div>
                    <div class="volume-slider-wrapper">
                        <label style="font-size: 0.7rem; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">VOLUME</label>
                        <input type="range" id="ambient-volume" class="volume-range" min="0" max="1" step="0.05" value="0.4">
                    </div>
                </div>

                <button id="open-search-modal" class="nav-icon-btn" title="Search Archive (Ctrl+K)"><i class="fa-solid fa-magnifying-glass"></i></button>
                <button id="open-favorites-modal" class="nav-icon-btn" title="Saved Places">
                    <i class="fa-solid fa-bookmark"></i>
                    <span class="nav-badge-count favorites-count-badge" style="display: none;">0</span>
                </button>
                <button class="mobile-nav-toggle"><i class="fa-solid fa-bars"></i></button>
            </div>
        </div>
    </header>

    <!-- Global Search Modal -->
    <div id="global-search-modal" class="modal-overlay">
        <div class="modal-content">
            <button id="close-search-modal" class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; font-family: var(--font-serif);">Search the Shadows</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">Explore places, districts, ruins, and legends across Bangladesh</p>
            <form action="/explore.php" method="GET" style="position: relative;">
                <input type="text" id="modal-search-input" name="search" class="form-control" placeholder="Search by name, district, or keyword..." style="padding-left: 2.75rem;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                <div style="margin-top: 1rem; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary btn-sm">Search Archive</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Favorites Modal -->
    <div id="favorites-modal" class="modal-overlay">
        <div class="modal-content">
            <button id="close-favorites-modal" class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-bookmark" style="color: var(--accent-red); font-size: 1.25rem;"></i>
                <h3 style="font-size: 1.35rem; font-family: var(--font-serif); margin: 0;">Bookmarked Sanctuaries</h3>
            </div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">Stored locally in your browser.</p>
            <div id="favorites-list-container" style="max-height: 360px; overflow-y: auto; margin-bottom: 1.5rem;"></div>
            <div style="display: flex; justify-content: space-between;">
                <a href="/explore.php" class="btn btn-outline-red btn-sm">Explore More Places</a>
                <a href="/map.php" class="btn btn-primary btn-sm">View on Map</a>
            </div>
        </div>
    </div>

    <!-- Main Dynamic Body -->
    ${bodyContent}

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="brand-logo" style="margin-bottom: 0.5rem;">
                        <div class="logo-symbol"><i class="fa-solid fa-ghost"></i></div>
                        <div class="brand-text">
                            <span class="brand-name">SHADOWS OF BANGLA</span>
                            <span class="brand-tagline-nav">Beyond the Known.</span>
                        </div>
                    </div>
                    <p>A digital archive documenting mysterious places, abandoned architectures, and oral folklore of Bangladesh, separating documented history from regional legends.</p>
                </div>
                <div>
                    <h4 class="footer-title">Archive Index</h4>
                    <ul class="footer-links">
                        <li><a href="/">Home Portal</a></li>
                        <li><a href="/explore.php">Explore Locations</a></li>
                        <li><a href="/map.php">Interactive Map</a></li>
                        <li><a href="/stories.php">Mystery Stories</a></li>
                        <li><a href="/submit-story.php">Submit a Legend</a></li>
                        <li><a href="/about.php">Research Mission</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title">Divisions</h4>
                    <ul class="footer-links">
                        <li><a href="/explore.php?division=Dhaka">Dhaka Division</a></li>
                        <li><a href="/explore.php?division=Chattogram">Chattogram Division</a></li>
                        <li><a href="/explore.php?division=Rajshahi">Rajshahi Division</a></li>
                        <li><a href="/explore.php?division=Khulna">Khulna Division</a></li>
                        <li><a href="/explore.php?division=Sylhet">Sylhet Division</a></li>
                        <li><a href="/explore.php?division=Barishal">Barishal Division</a></li>
                        <li><a href="/explore.php?division=Rangpur">Rangpur Division</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title">Content Standards</h4>
                    <p style="font-size: 0.85rem; line-height: 1.6; margin-bottom: 1rem;">
                        All historical narratives are corroborated through archaeological records. Spectral claims are presented strictly as cultural folklore.
                    </p>
                    <div style="background: rgba(23, 25, 34, 0.8); border: 1px solid var(--border-subtle); padding: 0.75rem; border-radius: 6px; font-size: 0.75rem;">
                        <span style="color: var(--accent-red); font-weight: 700;">Admin Access:</span>
                        <a href="/admin/login.php" style="color: #fff; text-decoration: underline; margin-left: 0.35rem;">Archivist Login</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div>&copy; 2026 <strong>SHADOWS OF BANGLA</strong>. Dedicated to the cultural and architectural preservation of Bangladesh.</div>
                <div>Map Data &copy; OpenStreetMap contributors &copy; CARTO</div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script id="map-places-data" type="application/json">${mapPlacesJson}</script>
    <script>window.SHADOWS_PLACES_CATALOG = ${mapPlacesJson};</script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/map.js"></script>
    <script src="/assets/js/interactions.js"></script>
</body>
</html>`;
}

// Request Handler
const server = http.createServer((req, res) => {
  const parsedUrl = url.parse(req.url, true);
  let pathname = parsedUrl.pathname;

  // Static Asset Serving
  if (pathname.startsWith('/assets/') || pathname === '/favicon.ico') {
    const filePath = path.join(ROOT_DIR, pathname);
    if (fs.existsSync(filePath) && fs.statSync(filePath).isFile()) {
      const ext = path.extname(filePath);
      const mime = MIME_TYPES[ext] || 'application/octet-stream';
      res.writeHead(200, { 'Content-Type': mime });
      return fs.createReadStream(filePath).pipe(res);
    }
  }

  // 1. HOME PORTAL (/)
  if (pathname === '/' || pathname === '/index.php') {
    const featuredPlaces = places.slice(0, 6);
    const recentStories = stories.slice(0, 3);
    const mysteryOfDay = places[0];

    const body = `
      <section class="hero-section">
          <img src="/assets/images/hero-bg.svg" alt="Atmospheric Bangladeshi Mystery" class="hero-backdrop-image">
          <div class="hero-overlay"></div>
          <div class="hero-vignette"></div>

          <div class="container hero-content">
              <div class="hero-badge"><i class="fa-solid fa-compass"></i> Documented Lore & Heritage Archive</div>
              <h1 class="hero-title">SHADOWS OF BANGLA</h1>
              <p class="hero-tagline">Beyond the Known.</p>
              <p class="hero-quote">&ldquo;Every place has a story. Some are better left unexplored.&rdquo;</p>
              <div class="hero-cta-group">
                  <a href="/explore.php" class="btn btn-primary"><i class="fa-solid fa-compass"></i> Explore the Unknown</a>
                  <a href="/stories.php" class="btn btn-secondary"><i class="fa-solid fa-book-open"></i> Discover the Stories</a>
                  <button class="btn btn-outline-red btn-summon-random"><i class="fa-solid fa-dice-d20"></i> Summon Random Place</button>
              </div>
          </div>
      </section>

      <section class="discovery-strip">
          <div class="container">
              <div class="summon-box">
                  <div class="summon-info">
                      <h3>Unravel a Random Enigma</h3>
                      <p>Dare to let fate select your next destination across the 8 divisions of Bangladesh.</p>
                  </div>
                  <div><button class="btn btn-primary btn-summon-random"><i class="fa-solid fa-wand-magic-sparkles"></i> Summon the Unknown</button></div>
              </div>
          </div>
      </section>

      <section class="section-padding" style="padding-bottom: 2rem;">
          <div class="container">
              <div class="mystery-of-day-banner">
                  <div class="banner-badge"><i class="fa-solid fa-moon"></i> Mystery of the Day</div>
                  <div class="mystery-grid">
                      <div class="mystery-img-wrap">
                          <img src="${mysteryOfDay.cover_image}" alt="${mysteryOfDay.name}">
                      </div>
                      <div>
                          <div style="color: var(--accent-red); font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">
                              <i class="fa-solid fa-location-dot"></i> ${mysteryOfDay.district}, ${mysteryOfDay.division} Division
                          </div>
                          <h2 style="font-size: 2rem; margin-bottom: 0.75rem;">${mysteryOfDay.name}</h2>
                          <p style="color: var(--text-secondary); margin-bottom: 1.5rem; line-height: 1.7;">${mysteryOfDay.summary}</p>
                          <a href="/place.php?slug=${mysteryOfDay.slug}" class="btn btn-primary">Enter the Sanctum <i class="fa-solid fa-arrow-right"></i></a>
                      </div>
                  </div>
              </div>
          </div>
      </section>

      <section id="featured-places" class="section-padding">
          <div class="container">
              <div class="section-header text-center">
                  <span class="section-subtitle">Documented Relics</span>
                  <h2 class="section-title">Featured Mysterious Locations</h2>
                  <p class="section-description">Sites where verified historical architecture intertwines with centuries of oral folklore.</p>
              </div>

              <div class="cards-grid">
                  ${featuredPlaces.map(p => `
                      <article class="place-card">
                          <div class="card-media">
                              <img src="${p.cover_image}" alt="${p.name}" class="card-image">
                              <div class="card-media-overlay"></div>
                              <div class="card-badges">
                                  <span class="category-pill"><i class="fa-solid ${p.category_icon}"></i> ${p.category_name}</span>
                                  <button class="bookmark-btn" data-id="${p.id}" data-slug="${p.slug}" data-name="${p.name}" data-district="${p.district}" data-division="${p.division}" data-image="${p.cover_image}" data-category="${p.category_name}"><i class="fa-regular fa-bookmark"></i></button>
                              </div>
                          </div>
                          <div class="card-body">
                              <div class="card-location"><i class="fa-solid fa-location-dot"></i> ${p.district}, ${p.division}</div>
                              <h3 class="card-title"><a href="/place.php?slug=${p.slug}">${p.name}</a></h3>
                              <p class="card-text">${p.summary}</p>
                              <div class="card-footer">
                                  <span class="status-badge badge-verified">${p.verification_status}</span>
                                  <a href="/place.php?slug=${p.slug}" class="btn btn-outline-red btn-sm">View Details</a>
                              </div>
                          </div>
                      </article>
                  `).join('')}
              </div>
              <div style="text-align: center; margin-top: 3.5rem;">
                  <a href="/explore.php" class="btn btn-secondary">View All Documented Places <i class="fa-solid fa-arrow-right"></i></a>
              </div>
          </div>
      </section>

      <!-- Map Preview -->
      <section class="section-padding" style="background: var(--bg-secondary);">
          <div class="container">
              <div class="section-header text-center">
                  <span class="section-subtitle">Cartographic Expedition</span>
                  <h2 class="section-title">The Shadows of Bangladesh</h2>
                  <p class="section-description">Survey verified enigmatic sites across all 8 administrative divisions of Bengal.</p>
              </div>
              <div class="map-container-box">
                  <div class="map-toolbar">
                      <div class="map-filters">
                          <select id="map-filter-division" class="map-select">
                              <option value="">All Divisions</option>
                              <option value="Dhaka">Dhaka Division</option>
                              <option value="Chattogram">Chattogram Division</option>
                              <option value="Rajshahi">Rajshahi Division</option>
                              <option value="Khulna">Khulna Division</option>
                              <option value="Sylhet">Sylhet Division</option>
                              <option value="Barishal">Barishal Division</option>
                              <option value="Rangpur">Rangpur Division</option>
                          </select>
                          <input type="text" id="map-search-input" class="map-select" placeholder="Filter map by keyword...">
                      </div>
                      <div style="display: flex; gap: 0.5rem;">
                          <button id="map-reset-btn" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrows-rotate"></i> Reset View</button>
                          <a href="/map.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-expand"></i> Full Map</a>
                      </div>
                  </div>
                  <div id="bangladesh-map"></div>
              </div>
          </div>
      </section>

      <!-- Quiz -->
      <section class="section-padding">
          <div class="container">
              <div class="section-header text-center">
                  <span class="section-subtitle">Trial of the Archivist</span>
                  <h2 class="section-title">The Enigma Challenge</h2>
                  <p class="section-description">Test your understanding of Bangladesh’s ancient civilizations and folklore traditions.</p>
              </div>
              <div id="interactive-quiz-container"></div>
          </div>
      </section>

      <!-- Stats -->
      <section class="stats-section">
          <div class="container">
              <div class="stats-grid">
                  <div class="stat-box"><div class="stat-number" data-target="14">0</div><div class="stat-label">Documented Locations</div></div>
                  <div class="stat-box"><div class="stat-number" data-target="5">0</div><div class="stat-label">Historical Chronicles</div></div>
                  <div class="stat-box"><div class="stat-number" data-target="8">0</div><div class="stat-label">Divisions Explored</div></div>
                  <div class="stat-box"><div class="stat-number" data-target="12">0</div><div class="stat-label">Districts Charted</div></div>
                  <div class="stat-box"><div class="stat-number" data-target="19420">0</div><div class="stat-label">Expedition Views</div></div>
              </div>
          </div>
      </section>
    `;

    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end(renderPage('Home', 'home', body));
  }

  // 2. EXPLORE PLACES (/explore.php)
  if (pathname === '/explore.php') {
    const q = (parsedUrl.query.search || '').toLowerCase();
    const div = (parsedUrl.query.division || '').toLowerCase();
    const cat = (parsedUrl.query.category || '').toLowerCase();

    let filtered = places.filter(p => {
      const matchQ = !q || p.name.toLowerCase().includes(q) || p.district.toLowerCase().includes(q) || p.summary.toLowerCase().includes(q);
      const matchDiv = !div || p.division.toLowerCase() === div;
      const matchCat = !cat || p.category_slug.toLowerCase() === cat;
      return matchQ && matchDiv && matchCat;
    });

    const body = `
      <section style="padding: 7.5rem 0 3.5rem; background: #11131a; border-bottom: 1px solid var(--border-subtle);">
          <div class="container text-center">
              <span class="section-subtitle">Discovery Compendium</span>
              <h1 class="section-title" style="font-size: 2.75rem;">Documented Sanctuaries</h1>
              <p class="section-description">Explore 14 verified locations across Bangladesh. Filter by division, classification, or archaeological status.</p>
          </div>
      </section>

      <section style="background: var(--bg-card); border-bottom: 1px solid var(--border-subtle); padding: 1.5rem 0; position: sticky; top: 70px; z-index: 100;">
          <div class="container">
              <form method="GET" action="/explore.php" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                  <input type="text" name="search" class="form-control" placeholder="Search by place or town..." value="${parsedUrl.query.search || ''}" style="max-width: 260px;">
                  <select name="division" class="map-select" onchange="this.form.submit()">
                      <option value="">All Divisions</option>
                      <option value="Dhaka" ${div === 'dhaka' ? 'selected' : ''}>Dhaka</option>
                      <option value="Chattogram" ${div === 'chattogram' ? 'selected' : ''}>Chattogram</option>
                      <option value="Rajshahi" ${div === 'rajshahi' ? 'selected' : ''}>Rajshahi</option>
                      <option value="Khulna" ${div === 'khulna' ? 'selected' : ''}>Khulna</option>
                      <option value="Sylhet" ${div === 'sylhet' ? 'selected' : ''}>Sylhet</option>
                      <option value="Barishal" ${div === 'barishal' ? 'selected' : ''}>Barishal</option>
                      <option value="Rangpur" ${div === 'rangpur' ? 'selected' : ''}>Rangpur</option>
                  </select>
                  <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                  <a href="/explore.php" class="btn btn-secondary btn-sm">Reset</a>
              </form>
          </div>
      </section>

      <section class="section-padding">
          <div class="container">
              <div style="margin-bottom: 2rem; color: var(--text-secondary);">Showing <strong>${filtered.length}</strong> documented mysterious sites</div>
              <div class="cards-grid">
                  ${filtered.map(p => `
                      <article class="place-card">
                          <div class="card-media">
                              <img src="${p.cover_image}" alt="${p.name}" class="card-image">
                              <div class="card-media-overlay"></div>
                              <div class="card-badges">
                                  <span class="category-pill">${p.category_name}</span>
                                  <button class="bookmark-btn" data-id="${p.id}" data-slug="${p.slug}" data-name="${p.name}" data-district="${p.district}" data-division="${p.division}" data-image="${p.cover_image}" data-category="${p.category_name}"><i class="fa-regular fa-bookmark"></i></button>
                              </div>
                          </div>
                          <div class="card-body">
                              <div class="card-location"><i class="fa-solid fa-location-dot"></i> ${p.district}, ${p.division}</div>
                              <h3 class="card-title"><a href="/place.php?slug=${p.slug}">${p.name}</a></h3>
                              <p class="card-text">${p.summary}</p>
                              <div class="card-footer">
                                  <span class="status-badge badge-verified">${p.verification_status}</span>
                                  <a href="/place.php?slug=${p.slug}" class="btn btn-outline-red btn-sm">View Details</a>
                              </div>
                          </div>
                      </article>
                  `).join('')}
              </div>
          </div>
      </section>
    `;

    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end(renderPage('Explore Places', 'explore', body));
  }

  // 3. MAP (/map.php)
  if (pathname === '/map.php') {
    const body = `
      <section style="padding: 7rem 0 2rem; background: #11131a; border-bottom: 1px solid var(--border-subtle);">
          <div class="container text-center">
              <span class="section-subtitle">Cartographic Archive</span>
              <h1 class="section-title">Interactive Map of Bangladesh</h1>
              <p class="section-description">Explore verified historical and folklore sites charted across the eight divisions of Bengal.</p>
          </div>
      </section>

      <section style="padding: 2rem 0 4rem;">
          <div class="container">
              <div class="map-container-box">
                  <div class="map-toolbar">
                      <div class="map-filters">
                          <select id="map-filter-division" class="map-select">
                              <option value="">All 8 Divisions</option>
                              <option value="Dhaka">Dhaka Division</option>
                              <option value="Chattogram">Chattogram Division</option>
                              <option value="Rajshahi">Rajshahi Division</option>
                              <option value="Khulna">Khulna Division</option>
                              <option value="Sylhet">Sylhet Division</option>
                              <option value="Barishal">Barishal Division</option>
                              <option value="Rangpur">Rangpur Division</option>
                          </select>
                          <input type="text" id="map-search-input" class="map-select" placeholder="Search by name or district...">
                      </div>
                      <div style="display: flex; gap: 0.5rem;">
                          <button id="map-reset-btn" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrows-rotate"></i> Reset View</button>
                          <a href="/explore.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-list"></i> Grid View</a>
                      </div>
                  </div>
                  <div id="bangladesh-map" style="height: 680px;"></div>
              </div>
          </div>
      </section>
    `;

    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end(renderPage('Interactive Map', 'map', body));
  }

  // 4. PLACE DETAILS (/place.php)
  if (pathname === '/place.php') {
    const slug = parsedUrl.query.slug || '';
    const place = places.find(p => p.slug === slug) || places[0];

    const body = `
      <div id="current-place-data" data-id="${place.id}" data-slug="${place.slug}" data-name="${place.name}" data-image="${place.cover_image}" data-district="${place.district}" data-division="${place.division}" style="display: none;"></div>

      <section class="place-hero">
          <img src="${place.cover_image}" alt="${place.name}" class="place-hero-img">
          <div class="place-hero-overlay"></div>
          <div class="container" style="position: relative; z-index: 10;">
              <div style="margin-bottom: 1rem;">
                  <a href="/explore.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Explore</a>
              </div>
              <h1 style="font-size: clamp(2rem, 5vw, 3.5rem); margin-bottom: 0.75rem;">${place.name}</h1>
              <div style="display: flex; gap: 1.5rem; color: var(--fog-grey); flex-wrap: wrap;">
                  <div><i class="fa-solid fa-location-dot" style="color: var(--accent-red);"></i> ${place.district}, ${place.division}</div>
                  <div><i class="fa-solid fa-compass" style="color: var(--accent-red);"></i> ${place.latitude}° N, ${place.longitude}° E</div>
                  <div><i class="fa-solid fa-shield-halved" style="color: var(--accent-red);"></i> ${place.verification_status}</div>
              </div>
          </div>
      </section>

      <section class="section-padding">
          <div class="container">
              <div class="place-details-grid">
                  <div>
                      <div class="content-box">
                          <h2><i class="fa-solid fa-landmark-dome text-red"></i> The Mystery at a Glance</h2>
                          <p class="prose" style="font-size: 1.15rem; color: #fff;">${place.description}</p>
                      </div>

                      <div class="content-box">
                          <h2><i class="fa-solid fa-shield-halved text-red"></i> Verified History</h2>
                          <div class="prose"><p>${place.historical_background}</p></div>
                      </div>

                      <div class="content-box">
                          <h2><i class="fa-solid fa-scroll text-red"></i> Local Folklore & Oral Traditions</h2>
                          <div class="prose"><p>${place.local_legends}</p></div>
                      </div>

                      ${place.paranormal_claims ? `
                          <div class="content-box">
                              <h2><i class="fa-solid fa-eye-slash text-red"></i> Alleged Paranormal Claims</h2>
                              <blockquote style="border-left: 3px solid var(--accent-red); padding-left: 1rem; color: var(--fog-grey); margin-bottom: 1rem; font-style: italic;">
                                  Notice: Paranormal claims are cataloged as witness folklore and not scientifically proven facts.
                              </blockquote>
                              <div class="prose"><p>${place.paranormal_claims}</p></div>
                          </div>
                      ` : ''}

                      <div class="content-box" style="background: rgba(17, 19, 26, 0.7);">
                          <h3><i class="fa-solid fa-book-bookmark text-red"></i> Primary Sources & References</h3>
                          <div style="font-size: 0.85rem; color: var(--text-muted); font-family: monospace;">${place.sources}</div>
                      </div>
                  </div>

                  <aside class="details-sidebar">
                      <div class="sidebar-sticky">
                          <div class="content-box" style="text-align: center; margin-bottom: 1.5rem;">
                              <button class="btn btn-primary bookmark-btn" style="width: 100%; margin-bottom: 0.75rem;" data-id="${place.id}" data-slug="${place.slug}" data-name="${place.name}" data-district="${place.district}" data-division="${place.division}" data-image="${place.cover_image}" data-category="${place.category_name}">
                                  <i class="fa-regular fa-bookmark"></i> Bookmark Location
                              </button>
                          </div>
                          <div class="content-box">
                              <h3><i class="fa-solid fa-map-pin text-red"></i> Coordinates Map</h3>
                              <div id="place-mini-map" data-lat="${place.latitude}" data-lng="${place.longitude}" data-name="${place.name}"></div>
                              <div style="margin-top: 1rem; font-size: 0.82rem; color: var(--text-muted);">
                                  <div><strong>District:</strong> ${place.district}</div>
                                  <div><strong>Division:</strong> ${place.division}</div>
                                  <div style="margin-top: 0.5rem;"><a href="https://www.google.com/maps?q=${place.latitude},${place.longitude}" target="_blank" style="color: var(--accent-red);">Open in Google Maps <i class="fa-solid fa-arrow-up-right-from-square"></i></a></div>
                              </div>
                          </div>
                      </div>
                  </aside>
              </div>
          </div>
      </section>
    `;

    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end(renderPage(place.name, 'explore', body));
  }

  // 5. STORIES (/stories.php)
  if (pathname === '/stories.php') {
    const body = `
      <section style="padding: 7.5rem 0 3.5rem; background: #11131a; border-bottom: 1px solid var(--border-subtle);">
          <div class="container text-center">
              <span class="section-subtitle">Literary Archive</span>
              <h1 class="section-title">Chronicles of the Dark</h1>
              <p class="section-description">Editorial deep dives into forgotten histories, abandoned dynasties, and the spectral folklore of the Bengal delta.</p>
          </div>
      </section>

      <section class="section-padding">
          <div class="container">
              <div class="cards-grid">
                  ${stories.map(st => `
                      <article class="story-card">
                          <div class="card-media">
                              <img src="${st.cover_image}" alt="${st.title}" class="card-image">
                              <div class="card-media-overlay"></div>
                              <div class="card-badges"><span class="category-pill">${st.category_name}</span></div>
                          </div>
                          <div class="card-body">
                              <div class="story-meta">
                                  <span><i class="fa-regular fa-clock"></i> ${st.reading_time} min read</span>
                              </div>
                              <h3 class="card-title"><a href="/story.php?slug=${st.slug}">${st.title}</a></h3>
                              <p class="card-text">${st.excerpt}</p>
                              <div class="card-footer">
                                  <span style="font-size: 0.8rem; color: var(--text-muted);">${st.author}</span>
                                  <a href="/story.php?slug=${st.slug}" class="btn btn-outline-red btn-sm">Read Chronicle</a>
                              </div>
                          </div>
                      </article>
                  `).join('')}
              </div>
          </div>
      </section>
    `;

    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end(renderPage('Chronicles', 'stories', body));
  }

  // 6. SINGLE STORY (/story.php)
  if (pathname === '/story.php') {
    const slug = parsedUrl.query.slug || '';
    const story = stories.find(s => s.slug === slug) || stories[0];

    const body = `
      <header style="padding: 9rem 0 4rem; background: #11131a; border-bottom: 1px solid var(--border-subtle);">
          <div class="container" style="max-width: 860px;">
              <div style="margin-bottom: 1rem;"><a href="/stories.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> All Chronicles</a></div>
              <h1 style="font-size: clamp(2.2rem, 4.5vw, 3.25rem); font-family: var(--font-serif); margin-bottom: 1rem;">${story.title}</h1>
              <div style="font-size: 0.85rem; color: var(--text-muted);">By <strong>${story.author}</strong> &bull; ${story.reading_time} min read</div>
          </div>
      </header>
      <article class="section-padding">
          <div class="container" style="max-width: 860px;">
              <div style="border-radius: 10px; overflow: hidden; margin-bottom: 2.5rem; border: 1px solid var(--border-subtle);">
                  <img src="${story.cover_image}" alt="${story.title}" style="width: 100%; height: 420px; object-fit: cover;">
              </div>
              <div style="background: var(--bg-card); border-left: 4px solid var(--accent-red); padding: 1.5rem; margin-bottom: 2.5rem; font-style: italic; color: var(--fog-grey); font-size: 1.15rem;">
                  ${story.excerpt}
              </div>
              <div class="prose" style="font-size: 1.15rem; line-height: 1.9; color: var(--text-main);">
                  ${story.content}
              </div>
          </div>
      </article>
    `;

    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end(renderPage(story.title, 'stories', body));
  }

  // 7. SUBMIT STORY (/submit-story.php)
  if (pathname === '/submit-story.php') {
    let successBanner = '';
    if (req.method === 'POST') {
      successBanner = `<div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; text-align: center;">
          <h3><i class="fa-solid fa-circle-check"></i> Chronicle Received</h3>
          <p>Your submission has been safely recorded in the archive and sent to curators for review.</p>
      </div>`;
    }

    const body = `
      <section style="padding: 7.5rem 0 3rem; background: #11131a; border-bottom: 1px solid var(--border-subtle);">
          <div class="container text-center" style="max-width: 800px;">
              <span class="section-subtitle">Community Chronicles</span>
              <h1 class="section-title">Submit a Local Mystery</h1>
              <p class="section-description">Do you know of an abandoned zamindar mansion or an oral legend whispered in your village? Preserve Bengal’s hidden stories.</p>
          </div>
      </section>

      <section class="section-padding">
          <div class="container" style="max-width: 800px;">
              ${successBanner}
              <div class="content-box">
                  <form action="/submit-story.php" method="POST">
                      <div class="form-group">
                          <label class="form-label">Your Name (Optional)</label>
                          <input type="text" name="submitter_name" class="form-control" placeholder="e.g. Tariqur Rahman">
                      </div>
                      <div class="form-group">
                          <label class="form-label">Story Title *</label>
                          <input type="text" name="story_title" class="form-control" required placeholder="e.g. The Spectral Bell of Batali Hill">
                      </div>
                      <div class="form-group">
                          <label class="form-label">District *</label>
                          <input type="text" name="district" class="form-control" required placeholder="e.g. Chattogram, Sylhet, Bogura">
                      </div>
                      <div class="form-group">
                          <label class="form-label">Story Details *</label>
                          <textarea name="story_content" class="form-control" rows="6" required placeholder="Narrate the folklore or personal encounter..."></textarea>
                      </div>
                      <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fa-solid fa-paper-plane"></i> Submit Chronicle to Archivists</button>
                  </form>
              </div>
          </div>
      </section>
    `;

    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end(renderPage('Submit Story', 'submit', body));
  }

  // 8. ABOUT (/about.php)
  if (pathname === '/about.php') {
    const body = `
      <section style="padding: 8.5rem 0 4rem; background: #11131a; border-bottom: 1px solid var(--border-subtle);">
          <div class="container text-center" style="max-width: 880px;">
              <span class="section-subtitle">Archival Ethos</span>
              <h1 class="section-title">Preserving the Forgotten Delta</h1>
              <p class="section-description" style="font-size: 1.2rem; color: var(--text-main); font-style: italic;">
                  &ldquo;Bangladesh is a land of ancient architecture, forgotten places, fascinating folklore, and unexplained stories. Shadows of Bangla is a digital journey through these mysteries. We explore the places, preserve their stories, and separate documented history from local legends.&rdquo;
              </p>
          </div>
      </section>

      <section class="section-padding">
          <div class="container" style="max-width: 900px;">
              <div class="content-box">
                  <h2><i class="fa-solid fa-compass text-red"></i> Our Mission</h2>
                  <div class="prose">
                      <p>SHADOWS OF BANGLA was established to build a dignified digital repository for the fading architectural and folk history of Bangladesh.</p>
                  </div>
              </div>
              <div class="content-box">
                  <h2><i class="fa-solid fa-microscope text-red"></i> Epistemic Labels</h2>
                  <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                      <div style="background: var(--bg-secondary); border-left: 4px solid #10b981; padding: 1rem;">
                          <h4 style="color: #34d399;">VERIFIED HISTORY</h4>
                          <p style="font-size: 0.9rem; margin: 0;">Corroborated by state archaeological surveys and academic historiography.</p>
                      </div>
                      <div style="background: var(--bg-secondary); border-left: 4px solid #f59e0b; padding: 1rem;">
                          <h4 style="color: #fbbf24;">LOCAL FOLKLORE</h4>
                          <p style="font-size: 0.9rem; margin: 0;">Oral cultural narratives and myths recorded from regional elders.</p>
                      </div>
                      <div style="background: var(--bg-secondary); border-left: 4px solid #ef4444; padding: 1rem;">
                          <h4 style="color: #f87171;">UNVERIFIED LEGEND</h4>
                          <p style="font-size: 0.9rem; margin: 0;">Anecdotal supernatural sightings explicitly labelled as unverified.</p>
                      </div>
                  </div>
              </div>
          </div>
      </section>
    `;

    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end(renderPage('About the Archive', 'about', body));
  }

  // 9. ADMIN DASHBOARD (/admin/ or /admin/index.php)
  if (pathname.startsWith('/admin')) {
    const adminBody = `
      <div style="display: flex; min-height: 100vh;">
          <aside style="width: 260px; background: #11131a; border-right: 1px solid var(--border-subtle); padding: 1.5rem;">
              <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem;">
                  <div class="logo-symbol" style="width: 32px; height: 32px;"><i class="fa-solid fa-ghost"></i></div>
                  <strong style="font-family: var(--font-serif); color: #fff;">ARCHIVIST</strong>
              </div>
              <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                  <a href="/admin/index.php" style="padding: 0.75rem; border-radius: 4px; background: rgba(158,27,50,0.2); color: #fff;"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                  <a href="/explore.php" style="padding: 0.75rem; color: var(--text-muted);"><i class="fa-solid fa-monument"></i> Places (${places.length})</a>
                  <a href="/stories.php" style="padding: 0.75rem; color: var(--text-muted);"><i class="fa-solid fa-book-skull"></i> Stories (${stories.length})</a>
                  <a href="/" style="padding: 0.75rem; color: var(--text-muted); margin-top: 2rem;"><i class="fa-solid fa-arrow-left"></i> View Site</a>
              </div>
          </aside>
          <main style="flex-grow: 1; padding: 2.5rem;">
              <h1 style="font-family: var(--font-serif); margin-bottom: 1.5rem;">Archivist Control Room</h1>
              <div class="admin-stats-grid">
                  <div class="admin-stat-card"><div class="stat-icon-wrap"><i class="fa-solid fa-monument"></i></div><div class="stat-details"><h3>${places.length}</h3><span>Documented Places</span></div></div>
                  <div class="admin-stat-card"><div class="stat-icon-wrap" style="color:#60a5fa;"><i class="fa-solid fa-book-skull"></i></div><div class="stat-details"><h3>${stories.length}</h3><span>Chronicles</span></div></div>
                  <div class="admin-stat-card"><div class="stat-icon-wrap" style="color:#facc15;"><i class="fa-solid fa-inbox"></i></div><div class="stat-details"><h3>${submissions.length}</h3><span>Submissions</span></div></div>
              </div>
              <div class="admin-card">
                  <h2>Documented Locations Database</h2>
                  <table class="admin-table" style="margin-top: 1rem;">
                      <thead><tr><th>Location</th><th>Division</th><th>Status</th><th>Coordinates</th></tr></thead>
                      <tbody>
                          ${places.map(p => `<tr><td><strong>${p.name}</strong></td><td>${p.division}</td><td><span class="pill-status pill-approved">${p.verification_status}</span></td><td>${p.latitude}, ${p.longitude}</td></tr>`).join('')}
                      </tbody>
                  </table>
              </div>
          </main>
      </div>
    `;

    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end(renderPage('Admin Dashboard', 'admin', adminBody));
  }

  // 10. Fallback 404
  res.writeHead(404, { 'Content-Type': 'text/html; charset=utf-8' });
  res.end(renderPage('404 Not Found', '', '<div class="container text-center" style="padding: 10rem 0;"><h1>404 — Lost in Fog</h1><p><a href="/" class="btn btn-primary" style="margin-top:1rem;">Return to Safety</a></p></div>'));
});

server.listen(PORT, '0.0.0.0', () => {
  console.log(`[SHADOWS OF BANGLA] Live server running at http://localhost:${PORT}`);
});

