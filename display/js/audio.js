/* ============================================================
   BANK BBJ - AUDIO PEMANGGILAN ANTRIAN
   File: display/js/audio.js
============================================================ */


/* ============================================================
   CONFIG
============================================================ */

const AUDIO_BASE = "audio/";

const AUDIO_ANGKA = {
    "0": AUDIO_BASE + "angka/nol.mp3",
    "1": AUDIO_BASE + "angka/satu.mp3",
    "2": AUDIO_BASE + "angka/dua.mp3",
    "3": AUDIO_BASE + "angka/tiga.mp3",
    "4": AUDIO_BASE + "angka/empat.mp3",
    "5": AUDIO_BASE + "angka/lima.mp3",
    "6": AUDIO_BASE + "angka/enam.mp3",
    "7": AUDIO_BASE + "angka/tujuh.mp3",
    "8": AUDIO_BASE + "angka/delapan.mp3",
    "9": AUDIO_BASE + "angka/sembilan.mp3"
};

const AUDIO_HURUF = {
    "A": AUDIO_BASE + "huruf/a.mp3",
    "B": AUDIO_BASE + "huruf/b.mp3",
    "C": AUDIO_BASE + "huruf/c.mp3"
};

const AUDIO_FRASA = {
    notif: AUDIO_BASE + "notif/ding-dong.mp3",
    nomorAntrian:
        AUDIO_BASE + "frasa/nomor-antrian.mp3",

    silakanMenujuLoket:
        AUDIO_BASE + "frasa/silakan-menuju-loket.mp3",

    suaraAktif:
        AUDIO_BASE + "frasa/suara-aktif.mp3"
};


/* ============================================================
   STATE
============================================================ */

const params = new URLSearchParams(window.location.search);
const isDisplayApp = params.get("app") === "1";
let soundEnabled = isDisplayApp;
let soundUnlocked = isDisplayApp;
let currentAudio = null;
let audioSequenceToken = 0;
let announcementId = 0;
let videoWasMuted = false;
let videoWasVolume = 1;
let videoMutedByQueue = false;

/* ============================================================
   DELAY
============================================================ */

function audioDelay(ms) {

    return new Promise(
        resolve =>
            setTimeout(
                resolve,
                ms
            )
    );

}


/* ============================================================
   STOP AUDIO
============================================================ */

function stopCurrentAudio() {

    audioSequenceToken++;

    if (currentAudio) {

        try {

            currentAudio.pause();
            currentAudio.currentTime = 0;

        } catch (_) {}

    }

    currentAudio = null;

}


/* ============================================================
   PLAY SINGLE FILE
============================================================ */

function playSingleAudio(
    src,
    token
) {

    return new Promise(
        resolve => {

            if (
                token !==
                audioSequenceToken
            ) {

                resolve();
                return;

            }

            const audio =
                new Audio(src);

            currentAudio =
                audio;

            audio.preload =
                "auto";

            audio.volume =
                1;


            audio.addEventListener(
                "ended",
                () => resolve(),
                {
                    once: true
                }
            );


            audio.addEventListener(
                "error",
                () => {

                    console.error(
                        "Audio tidak ditemukan:",
                        src
                    );

                    resolve();

                },
                {
                    once: true
                }
            );


            const promise =
                audio.play();


            if (
                promise !==
                undefined
            ) {

                promise.catch(
                    error => {

                        console.error(
                            "Audio diblokir browser:",
                            error
                        );

                        resolve();

                    }
                );

            }

        }
    );

}


/* ============================================================
   PLAY SEQUENCE
============================================================ */

async function playAudioSequence(
    sequence
) {

    if (
        !soundEnabled ||
        !soundUnlocked
    ) {
        return;
    }

    stopCurrentAudio();

    const token =
        audioSequenceToken;

    for (
        const item of sequence
    ) {

        if (
            token !==
            audioSequenceToken
        ) {
            return;
        }

        /*
         * Jika berupa angka,
         * dianggap delay milliseconds.
         */
        if (
            typeof item === "number"
        ) {

            await audioDelay(
                item
            );
            continue;
        }

        await playSingleAudio(
            item,
            token
        );
    }

    currentAudio = null;
}


/* ============================================================
   NOMOR ANTRIAN
============================================================ */

function buildNomorAudio(
    nomor
) {

    const sequence = [];


    if (!nomor) {
        return sequence;
    }


    /*
     * Contoh:
     *
     * A-015
     */

    const parts =
        String(nomor)
            .trim()
            .toUpperCase()
            .split("-");


    const huruf =
        parts[0] || "";


    const angka =
        parts[1] || "";


    /*
     * Huruf:
     * A / B / C
     */

    for (
        const char of huruf
    ) {

        if (
            AUDIO_HURUF[char]
        ) {

            sequence.push(
                AUDIO_HURUF[char]
            );

        }

    }


    /*
     * Jeda antara huruf
     * dengan angka.
     */

    sequence.push(
        120
    );


    /*
     * Angka dibaca digit per digit.
     *
     * 015 =
     * nol satu lima
     */

    for (
        const digit of angka
    ) {

        if (
            AUDIO_ANGKA[digit]
        ) {
            sequence.push(
                AUDIO_ANGKA[digit]
            );
        }
    }
    return sequence;
}

/* ============================================================
   NOMOR LOKET
============================================================ */

function buildLoketAudio(
    loket
) {

    const sequence = [];
    if (!loket) {
        return sequence;
    }

    /*
     * Contoh:
     *
     * LOKET 1
     *
     * mengambil angka "1".
     */

    const match =
        String(loket)
            .match(/\d+/);

    if (!match) {
        return sequence;
    }

    const nomor =
        match[0];

    for (
        const digit of nomor
    ) {
        if (
            AUDIO_ANGKA[digit]
        ) {
            sequence.push(
                AUDIO_ANGKA[digit]
            );
        }
    }
    return sequence;
}

/* ============================================================
   PEMANGGILAN UTAMA
============================================================ */
async function speakQueue(data) {
    if (!soundEnabled || !soundUnlocked || !data) return;
    const id = ++announcementId;
    muteVideoAudio();
    const sequence = [
        AUDIO_FRASA.notif,
        250,

        AUDIO_FRASA.nomorAntrian,
        180,

        ...buildNomorAudio(data.nomor_antrian),
        350,

        AUDIO_FRASA.silakanMenujuLoket,
        120,

        ...buildLoketAudio(data.loket)
    ];
    await playAudioSequence(sequence);
    if (id === announcementId) {
        restoreVideoAudio();
    }
}

/* ============================================================
   ENABLE SOUND
============================================================ */
async function enableQueueSound() {
    soundUnlocked = true;
    soundEnabled = true;
    const button =
        document.getElementById(
            "btnSound"
        );

    if (button) {
        button.textContent =
            "🔊 SUARA AKTIF";
        button.classList.remove(
            "off"
        );
    }

    stopCurrentAudio();
    const token =
        audioSequenceToken;
    await playSingleAudio(
        AUDIO_FRASA.suaraAktif,
        token
    );
}

/* ============================================================
   DISABLE SOUND
============================================================ */
function disableQueueSound() {
    soundEnabled = false;
    stopCurrentAudio();
    const button =
        document.getElementById(
            "btnSound"
        );

    if (button) {
        button.textContent =
            "🔇 SUARA MATI";
        button.classList.add(
            "off"
        );
    }
}

/* ============================================================
   TOGGLE SOUND
============================================================ */
async function toggleQueueSound() {
    if (
        !soundUnlocked
    ) {
        await enableQueueSound();
        return;
    }

    if (
        soundEnabled
    ) {
        disableQueueSound();
    } else {
        await enableQueueSound();
    }
}

function muteVideoAudio() {
    const video = document.getElementById("videoPlayer");
    if (!video) return;

    if (!videoMutedByQueue) {
        videoWasMuted = video.muted;
        videoWasVolume = video.volume;
        videoMutedByQueue = true;
    }

    video.muted = true;
}

function restoreVideoAudio() {
    const video = document.getElementById("videoPlayer");
    if (!video || !videoMutedByQueue) return;

    video.muted = videoWasMuted;
    video.volume = videoWasVolume;
    videoMutedByQueue = false;
}
/* ============================================================
   BUTTON EVENT
============================================================ */

document.addEventListener("DOMContentLoaded", () => {
    const button = document.getElementById("btnSound");
    if (!button) return;

    if (soundEnabled) {
        button.textContent = "🔊 SUARA AKTIF";
        button.classList.remove("off");
    } else {
        button.textContent = "🔊 AKTIFKAN SUARA";
        button.classList.add("off");
    }

    button.addEventListener("click", toggleQueueSound);
});