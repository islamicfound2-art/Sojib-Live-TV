const video = document.getElementById("video");
const playBtn = document.getElementById("playBtn");
const fullscreenBtn = document.getElementById("fullscreenBtn");
const muteBtn = document.getElementById("muteBtn");
const volSlider = document.getElementById("volSlider");
const volIcon = document.getElementById("volIcon");
const centerPlay = document.getElementById("centerPlay");
const search = document.getElementById("search");
const controls = document.getElementById("controls");
const buffering = document.getElementById("buffering");
const nowPlayingName = document.getElementById("nowPlayingName");
const channelGrid = document.getElementById("channelGrid");
const catTabs = document.querySelectorAll(".cat-tab");

let hls = null;
let hideTimer;
let currentCat = "popular";
let allChannels = [];     // cached list from api.php

// SVG icons
const ICONS = {
    play: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>`,
    pause: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>`,
    vol: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>`,
    mute: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/></svg>`,
    fsEnter: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>`,
    fsExit: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zM14 5v2h3v3h2V5h-5z"/></svg>`,
};

// ---------- PLACEHOLDER LOGO ----------
function placeholderLogo(name) {
    const label = (name || "TV").substring(0, 5).toUpperCase();
    const svg = `<svg xmlns='http://www.w3.org/2000/svg' width='60' height='60'><rect width='60' height='60' rx='30' fill='%23333'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' fill='white' font-size='10' font-family='Arial'>${label}</text></svg>`;
    return "data:image/svg+xml," + encodeURIComponent(svg);
}

// ---------- BUILD A CHANNEL CARD ----------
function buildChannelCard(ch) {
    const card = document.createElement("div");
    card.className = "channel-card";
    card.dataset.url = ch.url;
    card.dataset.name = ch.name;
    card.dataset.cat = ch.category;
    card.dataset.id = ch.id;

    const logoSrc = ch.logo ? ch.logo : placeholderLogo(ch.name);
    const fallback = placeholderLogo(ch.name);

    card.innerHTML = `
        ${ch.is_popular ? '<span class="popular-badge" title="Popular">⭐</span>' : ''}
        <div class="channel-logo">
            <img src="${logoSrc}" alt="${escapeHtml(ch.name)}"
                 onerror="this.onerror=null;this.src='${fallback}'">
        </div>
        <div class="channel-name">${escapeHtml(ch.name)}</div>
    `;

    card.addEventListener("click", () => {
        document.querySelector(".channel-card.active")?.classList.remove("active");
        card.classList.add("active");
        loadChannel(ch.url, ch.name);
        showControls();
    });

    return card;
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
}

// ---------- RENDER GRID (based on currentCat + search) ----------
function renderChannelGrid() {
    const q = search.value.toLowerCase();

    let list = allChannels;

    if (currentCat === "popular") {
        list = list.filter(ch => ch.is_popular);
    } else if (currentCat !== "all") {
        list = list.filter(ch => ch.category === currentCat);
    }

    if (q) {
        list = list.filter(ch => ch.name.toLowerCase().includes(q));
    }

    channelGrid.innerHTML = "";

    if (list.length === 0) {
        const empty = document.createElement("div");
        empty.className = "grid-empty";
        empty.textContent = "কোনো চ্যানেল পাওয়া যায়নি";
        channelGrid.appendChild(empty);
        return;
    }

    list.forEach(ch => channelGrid.appendChild(buildChannelCard(ch)));

    // Re-mark active card if it matches currently playing channel
    if (window.__currentPlayingUrl) {
        const match = [...channelGrid.querySelectorAll(".channel-card")]
            .find(c => c.dataset.url === window.__currentPlayingUrl);
        if (match) match.classList.add("active");
    }
}

// ---------- FETCH CHANNELS FROM API ----------
async function fetchChannels() {
    try {
        const res = await fetch("api.php");
        const data = await res.json();

        if (!data.ok || !data.channels) {
            channelGrid.innerHTML = '<div class="grid-empty">চ্যানেল লোড করা যায়নি</div>';
            return;
        }

        allChannels = data.channels;
        renderChannelGrid();

        // Auto-load first channel (prefer popular, else first in list)
        if (allChannels.length > 0) {
            const first = allChannels.find(ch => ch.is_popular) || allChannels[0];
            window.__currentPlayingUrl = first.url;
            loadChannel(first.url, first.name);
            const firstCard = [...channelGrid.querySelectorAll(".channel-card")]
                .find(c => c.dataset.url === first.url);
            if (firstCard) firstCard.classList.add("active");
        }
    } catch (e) {
        channelGrid.innerHTML = '<div class="grid-empty">সার্ভারের সাথে সংযোগ করা যায়নি</div>';
    }
}


// ---------- LOAD CHANNEL ----------
function loadChannel(url, name) {
    buffering.classList.add("show");
    window.__currentPlayingUrl = url;

    if (hls) { hls.destroy(); hls = null; }

    nowPlayingName.textContent = name || "Loading...";

    if (Hls.isSupported()) {
        hls = new Hls({
            lowLatencyMode: true,
            // ABR: network onujaye auto quality change hobe
            startLevel: -1,
            abrEwmaDefaultEstimate: 500000,
            abrBandWidthFactor: 0.95,
            abrBandWidthUpFactor: 0.7,
            abrMaxWithRealBitrate: true,
        });

        hls.loadSource(url);
        hls.attachMedia(video);

        // Load hole pause thakbe — user play korbe
        hls.on(Hls.Events.MANIFEST_PARSED, () => {
            buffering.classList.remove("show");
            video.pause();
            playBtn.innerHTML = ICONS.play;
            centerPlay.innerHTML = ICONS.play;
            controls.style.opacity = "1";
            centerPlay.style.opacity = "1";
            clearTimeout(hideTimer);
        });

        hls.on(Hls.Events.BUFFER_APPENDED, () => {
            buffering.classList.remove("show");
        });

        hls.on(Hls.Events.ERROR, (e, data) => {
            if (data.fatal) buffering.classList.remove("show");
        });

    } else if (video.canPlayType("application/vnd.apple.mpegurl")) {
        video.src = url;
        video.load();
        video.pause();
        playBtn.innerHTML = ICONS.play;
        centerPlay.innerHTML = ICONS.play;
        buffering.classList.remove("show");
    }
}


// ---------- INIT ----------
fetchChannels();


// ---------- CATEGORIES ----------
catTabs.forEach(tab => {
    tab.addEventListener("click", () => {
        catTabs.forEach(t => t.classList.remove("active"));
        tab.classList.add("active");
        currentCat = tab.dataset.cat;
        renderChannelGrid();
    });
});


// ---------- SEARCH ----------
search.addEventListener("input", renderChannelGrid);


// ---------- PLAY / PAUSE ----------
function togglePlay() {
    if (video.paused) video.play();
    else video.pause();
}

playBtn.addEventListener("click", togglePlay);
centerPlay.addEventListener("click", togglePlay);


// ---------- CONTROLS SHOW/HIDE ----------
let controlsVisible = true;

function showControls() {
    controls.style.opacity = "1";
    centerPlay.style.opacity = "1";
    controlsVisible = true;
    clearTimeout(hideTimer);

    if (!video.paused) {
        hideTimer = setTimeout(hideControls, 3000);
    }
}

function hideControls() {
    controls.style.opacity = "0";
    centerPlay.style.opacity = "0";
    controlsVisible = false;
}

// Desktop: mouse move shows controls
video.addEventListener("mousemove", showControls);

// Mobile: tap toggles controls visibility
// First tap = show, second tap = hide (or play/pause if already visible)
let touchToggleTimer;
video.addEventListener("touchend", (e) => {
    e.preventDefault();
    if (!controlsVisible) {
        // Controls hidden → show them
        showControls();
    } else {
        // Controls visible → toggle play/pause
        togglePlay();
    }
}, { passive: false });


// ---------- VIDEO EVENTS ----------
video.addEventListener("play", () => {
    playBtn.innerHTML = ICONS.pause;
    centerPlay.innerHTML = ICONS.pause.replace('viewBox', 'class="pause-icon" viewBox');
    buffering.classList.remove("show");
    showControls();
});

video.addEventListener("pause", () => {
    playBtn.innerHTML = ICONS.play;
    centerPlay.innerHTML = ICONS.play;
    controls.style.opacity = "1";
    centerPlay.style.opacity = "1";
    clearTimeout(hideTimer);
});

video.addEventListener("waiting", () => buffering.classList.add("show"));
video.addEventListener("canplay", () => buffering.classList.remove("show"));


// ---------- VOLUME ----------
volSlider.addEventListener("input", () => {
    const val = parseFloat(volSlider.value);
    video.volume = val;
    video.muted = val === 0;
    updateVolIcon();
});

muteBtn.addEventListener("click", () => {
    video.muted = !video.muted;
    volSlider.value = video.muted ? 0 : video.volume || 1;
    if (!video.muted && video.volume === 0) video.volume = 1;
    updateVolIcon();
});

function updateVolIcon() {
    const volPath = document.getElementById("volPath");
    if (!volPath) return;
    if (video.muted || video.volume === 0) {
        volPath.setAttribute("d", "M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z");
    } else if (video.volume < 0.5) {
        volPath.setAttribute("d", "M18.5 12c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM5 9v6h4l5 5V4L9 9H5z");
    } else {
        volPath.setAttribute("d", "M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z");
    }
}


// ---------- FULLSCREEN + AUTO ROTATE ----------
fullscreenBtn.addEventListener("click", async () => {
    const wrapper = document.querySelector(".video-wrapper");

    try {
        if (!document.fullscreenElement) {

            // Enter fullscreen
            if (wrapper.requestFullscreen) {
                await wrapper.requestFullscreen();
            } else if (wrapper.webkitRequestFullscreen) {
                wrapper.webkitRequestFullscreen();
            }

            // Rotate to landscape
            if (screen.orientation && screen.orientation.lock) {
                try {
                    await screen.orientation.lock("landscape");
                } catch (err) {
                    console.log("Orientation lock not supported");
                }
            }

        } else {

            // Exit fullscreen
            if (document.exitFullscreen) {
                await document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }

            // Back to portrait
            if (screen.orientation && screen.orientation.unlock) {
                screen.orientation.unlock();
            }
        }
    } catch (e) {
        console.log(e);
    }
});

// Change fullscreen icon
document.addEventListener("fullscreenchange", () => {
    fullscreenBtn.innerHTML =
        document.fullscreenElement ? ICONS.fsExit : ICONS.fsEnter;
});


// ---------- VIEWER COUNT ----------
if (!localStorage.getItem("viewer_id")) {
    localStorage.setItem(
        "viewer_id",
        Date.now() + Math.random().toString(36).substring(2)
    );
}

const viewerId = localStorage.getItem("viewer_id");

function updateViewers() {

    fetch("viewer.php?id=" + viewerId)
        .then(r => r.text())
        .then(count => {

            document.getElementById("viewerCount").textContent = count;

        });

}

updateViewers();

setInterval(updateViewers, 1000);
