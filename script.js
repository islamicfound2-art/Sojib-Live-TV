const video = document.getElementById("video");
const playBtn = document.getElementById("playBtn");
const fullscreenBtn = document.getElementById("fullscreenBtn");
const centerPlay = document.getElementById("centerPlay");
const search = document.getElementById("search");
const videoWrapper = document.getElementById("videoWrapper");
const bufferSpinner = document.getElementById("bufferSpinner");

const controls = document.querySelector(".controls");
const cards = document.querySelectorAll(".channel-card");

let hls = null;
let hideTimer;
let bufferStallTimer;


// ---------- SPINNER HELPERS ----------

function showSpinner() {
    bufferSpinner.classList.add("show");
}

function hideSpinner() {
    bufferSpinner.classList.remove("show");
}


// ---------- LOAD CHANNEL ----------

function loadChannel(url) {

    // Always tear down any previous instance/source first so the old
    // stream doesn't keep buffering in the background while a new one loads.
    if (hls) {
        hls.destroy();
        hls = null;
    }

    video.removeAttribute("src");
    video.load();

    showSpinner();

    if (Hls.isSupported()) {

        hls = new Hls({
            lowLatencyMode: true,
            // Faster initial start: begin at the lowest quality and let
            // ABR ramp up once buffer health is established.
            startLevel: 0,
            maxBufferLength: 20,
            maxMaxBufferLength: 30,
            backBufferLength: 30,
            liveSyncDurationCount: 3,
            enableWorker: true,
            fragLoadingTimeOut: 8000,
            manifestLoadingTimeOut: 8000,
            levelLoadingTimeOut: 8000,
        });

        hls.loadSource(url);
        hls.attachMedia(video);

        hls.on(Hls.Events.MANIFEST_PARSED, () => {
            video.play().catch(() => { });
        });

        hls.on(Hls.Events.ERROR, (event, data) => {
            if (!data.fatal) return;

            switch (data.type) {
                case Hls.ErrorTypes.NETWORK_ERROR:
                    hls.startLoad();
                    break;
                case Hls.ErrorTypes.MEDIA_ERROR:
                    hls.recoverMediaError();
                    break;
                default:
                    hls.destroy();
                    hls = null;
                    hideSpinner();
                    break;
            }
        });

    }
    else if (
        video.canPlayType("application/vnd.apple.mpegurl")
    ) {

        video.src = url;
        video.addEventListener("loadedmetadata", () => {
            video.play().catch(() => { });
        }, { once: true });

    }

}


// ---------- LOAD ACTIVE CHANNEL ----------

const activeCard =
    document.querySelector(".channel-card.active");

if (
    activeCard &&
    activeCard.dataset.url
) {

    loadChannel(activeCard.dataset.url);

}


// ---------- CHANNEL CLICK ----------

cards.forEach(card => {

    card.addEventListener("click", () => {

        const url = card.dataset.url;

        if (!url) return;

        document
            .querySelector(".channel-card.active")
            ?.classList.remove("active");

        card.classList.add("active");

        loadChannel(url);

        centerPlay.innerHTML = "❚❚";

        controls.style.opacity = "1";
        centerPlay.style.opacity = "1";

        clearTimeout(hideTimer);

        // Keep the chosen card in view, especially useful on mobile
        // where the channel grid sits below the player.
        card.scrollIntoView({ behavior: "smooth", block: "nearest" });

    });

});


// ---------- SEARCH ----------

search.addEventListener("input", () => {

    const value =
        search.value.toLowerCase();

    cards.forEach(card => {

        const name =
            card.querySelector(".channel-name")
                .innerText
                .toLowerCase();

        card.style.display =
            name.includes(value)
                ? ""
                : "none";

    });

});


// ---------- PLAY / PAUSE ----------

function togglePlay() {

    if (video.paused) {

        video.play().catch(() => { });

    }
    else {

        video.pause();

    }

}

playBtn.addEventListener(
    "click",
    togglePlay
);

centerPlay.addEventListener(
    "click",
    togglePlay
);


// ---------- SHOW CONTROLS ----------

function showControls() {

    controls.style.opacity = "1";

    centerPlay.style.opacity = "1";

    clearTimeout(hideTimer);

    if (!video.paused) {

        hideTimer = setTimeout(() => {

            controls.style.opacity = "0";

            centerPlay.style.opacity = "0";

        }, 3000);

    }

}


// ---------- VIDEO PLAY ----------

video.addEventListener("play", () => {

    playBtn.innerHTML = "❚❚";

    centerPlay.innerHTML = "❚❚";

    showControls();

});


// ---------- VIDEO PAUSE ----------

video.addEventListener("pause", () => {

    playBtn.innerHTML = "▶";

    centerPlay.innerHTML = "▶";

    controls.style.opacity = "1";

    centerPlay.style.opacity = "1";

    clearTimeout(hideTimer);

});


// ---------- BUFFERING INDICATOR ----------

video.addEventListener("waiting", () => {
    showSpinner();
});

video.addEventListener("playing", () => {
    hideSpinner();
});

video.addEventListener("canplay", () => {
    hideSpinner();
});

video.addEventListener("error", () => {
    hideSpinner();
});


// ---------- HOVER / TOUCH ----------

video.addEventListener(
    "mousemove",
    showControls
);

video.addEventListener(
    "touchstart",
    showControls
);

videoWrapper.addEventListener(
    "touchstart",
    showControls
);


// ---------- AUTO PAUSE WHEN TAB / APP IS HIDDEN ----------
// Saves bandwidth and battery on mobile when the user switches apps
// or locks the screen, and matches user expectation for a "live TV" app.

document.addEventListener("visibilitychange", () => {

    if (document.hidden) {

        if (!video.paused) {
            video.pause();
        }

    }

});


// ---------- FULLSCREEN + ANDROID AUTO-ROTATE ----------

function isFullscreen() {
    return !!(
        document.fullscreenElement ||
        document.webkitFullscreenElement
    );
}

async function enterFullscreen() {

    const el = videoWrapper;

    try {
        if (el.requestFullscreen) {
            await el.requestFullscreen();
        } else if (el.webkitRequestFullscreen) {
            el.webkitRequestFullscreen();
        } else if (video.webkitEnterFullscreen) {
            // iOS Safari fallback: fullscreens the <video> element itself
            video.webkitEnterFullscreen();
            return;
        }
    } catch (err) {
        // Fullscreen request can be rejected (e.g. not a direct user
        // gesture) — fail silently rather than breaking the UI.
    }

    // Android browsers don't auto-rotate video like iOS does, so we
    // explicitly lock to landscape once fullscreen is active.
    if (screen.orientation && screen.orientation.lock) {
        try {
            await screen.orientation.lock("landscape");
        } catch (err) {
            // Orientation lock can fail (e.g. desktop, or permission
            // denied) — that's fine, fullscreen still works.
        }
    }

}

function exitFullscreen() {

    if (document.exitFullscreen) {
        document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
    }

    if (screen.orientation && screen.orientation.unlock) {
        try {
            screen.orientation.unlock();
        } catch (err) { }
    }

}

fullscreenBtn.addEventListener(
    "click",
    () => {

        if (isFullscreen()) {
            exitFullscreen();
        } else {
            enterFullscreen();
        }

    }
);

// Unlock orientation automatically if the user exits fullscreen via
// the system back button / gesture rather than our button.
document.addEventListener("fullscreenchange", () => {

    if (!isFullscreen() && screen.orientation && screen.orientation.unlock) {
        try {
            screen.orientation.unlock();
        } catch (err) { }
    }

});

document.addEventListener("webkitfullscreenchange", () => {

    if (!isFullscreen() && screen.orientation && screen.orientation.unlock) {
        try {
            screen.orientation.unlock();
        } catch (err) { }
    }

});
