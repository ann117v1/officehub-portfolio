<?php
/** @var string|null $title */
/** @var string $content */
?>


<!DOCTYPE html>
<html lang="es" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'OfficeHub') ?></title>
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <link rel="stylesheet" id="hljs-theme" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <style>
        :root {
            --bg-base: #0d1117;
            --bg-surface: #161b22;
            --bg-hover: #1c2128;
            --bg-input: #0d1117;
            --border: #21262d;
            --border-2: #30363d;
            --text-1: #e6edf3;
            --text-2: #8b949e;
            --text-3: #484f58;
            --accent: #58a6ff;
            --accent-bg: #1f6feb;
            --accent-brd: #388bfd;
            --green: #3fb950;
            --green-bg: #1a3a1a;
            --purple: #a371f7;
            --purple-bg: #2d1f3d;
            --red: #f85149;
            --red-bg: #3a1a1a;
            --nav-bg: #1c2128;
            --nav-border: #2a2f35;
            --pill-bg: #0d1117;
            --pill-border: #30363d;
            --pill-active-bg: #0d1117;
            --pill-active-border: #30363d;
            --pill-active-color: #e6edf3;
            --star-bg-start: #1b2735;
            --star-bg-end: #090a0f;
            --star-color-1: rgba(255, 255, 255, 0.9);
            --star-color-2: rgba(155, 201, 255, 0.72);
            --star-color-3: rgba(88, 166, 255, 0.56);
            --star-glow: rgba(88, 166, 255, 0.18);
            --star-haze: rgba(0, 255, 170, 0.045);
        }

        [data-theme="light"] {
            --bg-base: #f8fafc;
            --bg-surface: #d2d7dd;
            --bg-hover: #eaeef2;
            --bg-input: #ffffff;
            --border: #bdc7d4;
            --border-2: #bdc7d4;
            --text-1: #1f2328;
            --text-2: #636c76;
            --text-3: #9198a1;
            --accent: #0969da;
            --accent-bg: #0969da;
            --accent-brd: #0969da;
            --green: #1a7f37;
            --green-bg: #dafbe1;
            --purple: #8250df;
            --purple-bg: #fbefff;
            --red: #d1242f;
            --red-bg: #ffebe9;
            --nav-bg: #d2d7dd;
            --nav-border: #bdc7d4;
            --pill-bg: #ffffff;
            --pill-border: #d0d0d0;
            --pill-active-bg: #1a1a1a;
            --pill-active-border: #1a1a1a;
            --pill-active-color: #ffffff;
            --star-bg-start: #cfe5ff;
            --star-bg-end: #f1f8ff;
            --star-color-1: rgba(9, 105, 218, 0.58);
            --star-color-2: rgba(31, 111, 235, 0.42);
            --star-color-3: rgba(88, 166, 255, 0.36);
            --star-glow: rgba(9, 105, 218, 0.22);
            --star-haze: rgba(88, 166, 255, 0.16);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--bg-base);
            color: var(--text-1);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: background-color 0.2s, color 0.2s;
            overflow-x: hidden;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-surface);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-2);
            border-radius: 3px;
        }

        /* Fondo estrellado global */
        .bg-grid {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
            background:
                radial-gradient(circle at 50% 115%, var(--star-glow) 0%, transparent 38%),
                radial-gradient(circle at 80% 15%, var(--star-haze) 0%, transparent 30%),
                radial-gradient(ellipse at bottom, var(--star-bg-start) 0%, var(--star-bg-end) 72%);
            background-size: cover, cover, cover;
        }

        .bg-grid::before,
        .bg-grid::after {
            content: "";
            position: absolute;
            inset: 0;
            opacity: 0.9;
            will-change: background-position;
        }

        .bg-grid::before {
            background-image:
                radial-gradient(circle, var(--star-color-1) 0 1px, transparent 1.5px),
                radial-gradient(circle, var(--star-color-2) 0 1px, transparent 1.5px),
                radial-gradient(circle, var(--star-color-3) 0 1px, transparent 1.5px);
            background-size: 140px 140px, 210px 210px, 310px 310px;
            background-position: 16px 22px, 90px 125px, 180px 48px;
            animation: starDriftFine 28s linear infinite;
        }

        .bg-grid::after {
            background-image:
                radial-gradient(circle, var(--star-color-1) 0 1.4px, transparent 2px),
                radial-gradient(circle, var(--star-color-2) 0 1.8px, transparent 2.6px);
            background-size: 260px 260px, 420px 420px;
            background-position: 44px 72px, 190px 12px;
            filter: drop-shadow(0 0 5px var(--star-glow));
            opacity: 0.72;
            animation: starDriftBright 48s linear infinite;
        }

        @keyframes starDriftFine {
            from {
                background-position: 16px 22px, 90px 125px, 180px 48px;
            }

            to {
                background-position: 16px -118px, 90px -85px, 180px -262px;
            }
        }

        @keyframes starDriftBright {
            from {
                background-position: 44px 72px, 190px 12px;
            }

            to {
                background-position: 44px -188px, 190px -408px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .bg-grid::before,
            .bg-grid::after {
                animation: none;
            }
        }

        .stars-paused .bg-grid::before,
        .stars-paused .bg-grid::after {
            animation-play-state: paused;
        }

        .navbar,
        main,
        footer {
            position: relative;
            z-index: 1;
        }

        /* Navbar pill */
        .navbar-wrap {
            padding: 12px 24px;
            display: flex;
            justify-content: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .navbar {
            background: var(--nav-bg);
            border: 1px solid var(--nav-border);
            border-radius: 40px;
            padding: 5px 6px;
            display: flex;
            align-items: center;
            gap: 2px;
            width: 100%;
            max-width: 900px;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 12px;
            margin-right: 4px;
            text-decoration: none;
        }

        .nav-logo-icon {
            width: 26px;
            height: 26px;
            background: var(--pill-bg);
            border: 1px solid var(--pill-border);
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .nav-logo-text {
            color: var(--text-1);
            font-size: 14px;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        .nav-divider {
            width: 1px;
            height: 16px;
            background: var(--border);
            margin: 0 4px;
            flex-shrink: 0;
        }

        .nav-pill {
            padding: 6px 13px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-2);
            text-decoration: none;
            border: 1px solid transparent;
            transition: background 0.15s, color 0.15s, border-color 0.15s;
            white-space: nowrap;
        }

        .nav-pill:hover {
            background: var(--bg-hover);
            color: var(--text-1);
            border-color: var(--border);
        }

        .nav-pill.active {
            background: var(--pill-active-bg);
            color: var(--pill-active-color);
            border-color: var(--pill-active-border);
        }

        .nav-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-right > .nav-theme,
        .nav-right > .nav-motion,
        .nav-right > .nav-logout {
            display: none;
        }

        .nav-user {
            font-size: 12px;
            color: var(--text-2);
            padding: 0 8px;
        }

        .nav-role {
            background: var(--accent-bg);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .nav-user-menu-wrap {
            position: relative;
        }

        .nav-user-trigger {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--pill-bg);
            border: 1px solid var(--nav-border);
            color: var(--text-1);
            border-radius: 999px;
            padding: 3px 8px 3px 4px;
            cursor: pointer;
            max-width: 190px;
            transition: border-color 0.15s, background 0.15s;
        }

        .nav-user-trigger:hover,
        .nav-user-trigger.is-open {
            border-color: var(--accent-brd);
            background: var(--bg-hover);
        }

        .nav-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--accent-bg);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.02em;
            flex-shrink: 0;
        }

        .nav-user-trigger-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-1);
            max-width: 110px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .nav-user-chevron {
            color: var(--text-2);
            transition: transform 0.2s;
            flex-shrink: 0;
        }

        .nav-user-trigger.is-open .nav-user-chevron {
            transform: rotate(180deg);
        }

        .nav-user-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 230px;
            background: var(--nav-bg);
            border: 1px solid var(--nav-border);
            border-radius: 14px;
            padding: 8px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.35);
            display: none;
            z-index: 125;
        }

        .nav-user-menu.open {
            display: block;
        }

        .nav-user-menu-head {
            padding: 10px 10px 12px;
        }

        .nav-user-menu-name {
            color: var(--text-1);
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .nav-user-menu-role {
            color: var(--text-2);
            font-size: 12px;
        }

        .nav-user-menu-sep {
            height: 1px;
            background: var(--border);
            margin: 4px 0;
        }

        .nav-user-menu-item {
            width: 100%;
            border: 0;
            background: transparent;
            color: var(--text-1);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 9px 10px;
            border-radius: 9px;
            font-size: 13px;
            cursor: pointer;
            text-align: left;
            font-family: inherit;
        }

        .nav-user-menu-item:hover {
            background: var(--bg-hover);
        }

        .nav-user-menu-item.is-disabled {
            color: var(--text-3);
            cursor: default;
        }

        .nav-user-menu-item.is-disabled:hover {
            background: transparent;
        }

        .nav-user-menu-item.is-danger {
            color: var(--red);
        }

        .nav-user-menu-muted {
            color: var(--text-3);
            font-size: 11px;
        }

        .nav-theme,
        .nav-motion {
            background: var(--pill-bg);
            border: 1px solid var(--nav-border);
            color: var(--text-2);
            font-size: 13px;
            padding: 4px 10px;
            border-radius: 20px;
            cursor: pointer;
            transition: color 0.15s, border-color 0.15s, box-shadow 0.15s;
        }

        .nav-theme:hover,
        .nav-motion:hover {
            color: var(--text-1);
        }

        .nav-motion.is-paused {
            color: var(--accent);
            border-color: var(--accent-brd);
            box-shadow: 0 0 0 2px rgba(88, 166, 255, 0.12);
        }

        .nav-notifications {
            position: relative;
        }

        .nav-bell {
            width: 30px;
            height: 30px;
            background: var(--pill-bg);
            border: 1px solid var(--nav-border);
            color: var(--text-2);
            border-radius: 50%;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s, border-color 0.15s, box-shadow 0.15s;
        }

        .nav-bell:hover,
        .nav-bell.is-open {
            color: var(--text-1);
            border-color: var(--accent-brd);
        }

        .nav-bell-count {
            position: absolute;
            top: -3px;
            right: -3px;
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            border-radius: 999px;
            background: var(--red);
            color: #fff;
            border: 2px solid var(--nav-bg);
            font-size: 10px;
            line-height: 12px;
            display: none;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .nav-bell-count.has-items {
            display: flex;
        }

        .notifications-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: min(340px, calc(100vw - 32px));
            background: var(--nav-bg);
            border: 1px solid var(--nav-border);
            border-radius: 14px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.35);
            padding: 8px;
            display: none;
            z-index: 120;
        }

        .notifications-dropdown.open {
            display: block;
        }

        .notifications-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 6px 8px 8px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 4px;
        }

        .notifications-head strong {
            font-size: 12px;
            color: var(--text-1);
        }

        .notifications-head button {
            background: transparent;
            border: none;
            color: var(--accent);
            font-size: 11px;
            cursor: pointer;
        }

        .notifications-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .notifications-desktop.is-hidden {
            display: none;
        }

        .notifications-desktop.is-active {
            color: var(--green);
            cursor: default;
        }

        .notifications-desktop.is-blocked {
            color: var(--red);
            cursor: default;
        }

        .notification-item {
            display: block;
            padding: 10px 8px;
            border-radius: 8px;
            text-decoration: none;
            border-bottom: 1px solid var(--border);
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background: var(--bg-hover);
        }

        .notification-title {
            color: var(--text-1);
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .notification-body {
            color: var(--text-2);
            font-size: 12px;
            line-height: 1.4;
            margin-bottom: 6px;
        }

        .notification-meta {
            color: var(--text-3);
            font-size: 11px;
        }

        .notification-empty {
            color: var(--text-2);
            font-size: 12px;
            padding: 14px 8px;
        }

        .nav-logout {
            padding: 6px 13px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 500;
            color: var(--text-2);
            border: 1px solid var(--nav-border);
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: transparent;
            transition: background 0.15s, color 0.15s;
        }

        .nav-logout:hover {
            background: var(--bg-hover);
            color: var(--text-1);
        }

        /* Dropdown áreas */
        .area-dropdown-wrap {
            position: relative;
        }

        .area-trigger {
            padding: 6px 13px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-2);
            border: 1px solid transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            background: transparent;
            transition: background 0.15s, color 0.15s, border-color 0.15s;
            user-select: none;
        }

        .area-trigger:hover {
            background: var(--bg-hover);
            color: var(--text-1);
            border-color: var(--border);
        }

        .area-trigger.has-area {
            color: var(--text-1);
        }

        .area-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .area-chevron {
            transition: transform 0.2s;
        }

        .area-chevron.open {
            transform: rotate(180deg);
        }

        .area-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            background: var(--nav-bg);
            border: 1px solid var(--nav-border);
            border-radius: 14px;
            padding: 6px;
            min-width: 200px;
            z-index: 100;
            display: none;
        }

        .area-dropdown.open {
            display: block;
        }

        .area-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-1);
            cursor: pointer;
            transition: background 0.15s;
        }

        .area-option:hover {
            background: var(--bg-hover);
        }

        .area-option.selected {
            background: var(--bg-hover);
            font-weight: 500;
        }

        .area-check {
            margin-left: auto;
            color: var(--accent);
            font-size: 12px;
        }

        .area-sep {
            height: 1px;
            background: var(--border);
            margin: 4px 0;
        }

        .area-label {
            font-size: 10px;
            color: var(--text-3);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 4px 12px 2px;
        }

        /* Flash messages */
        .flash-success {
            background: var(--green-bg);
            border: 1px solid var(--green);
            color: var(--green);
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 13px;
            max-height: 80px;
            overflow: hidden;
            transition: opacity 0.22s ease, transform 0.22s ease, max-height 0.22s ease, padding 0.22s ease, margin 0.22s ease;
        }

        .flash-error {
            background: var(--red-bg);
            border: 1px solid var(--red);
            color: var(--red);
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 13px;
            max-height: 80px;
            overflow: hidden;
            transition: opacity 0.22s ease, transform 0.22s ease, max-height 0.22s ease, padding 0.22s ease, margin 0.22s ease;
        }

        .flash-hide {
            opacity: 0;
            transform: translateY(-4px);
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .release-announcement {
            position: fixed;
            top: 56px;
            right: max(24px, calc((100vw - 1380px) / 2 + 24px));
            z-index: 90;
            width: min(540px, calc(100vw - 32px));
        }

        .release-announcement[hidden] {
            display: none;
        }

        .release-news-button {
            position: fixed;
            top: 16px;
            right: max(24px, calc((100vw - 1380px) / 2 + 24px));
            z-index: 91;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 34px;
            padding: 0 14px;
            border-radius: 999px;
            border: 1px solid var(--border-2);
            background: color-mix(in srgb, var(--bg-surface) 88%, transparent);
            color: var(--text-1);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.22);
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            backdrop-filter: blur(14px);
            transition: transform 0.16s, border-color 0.16s, background 0.16s, box-shadow 0.16s;
        }

        .release-news-button:hover,
        .release-news-button[aria-expanded="true"] {
            transform: translateY(-1px);
            border-color: var(--accent-brd);
            background: var(--bg-hover);
        }

        .release-news-button.is-unread {
            border-color: #ffcc00;
            color: #ffffff;
            background: linear-gradient(135deg, #ff2d55, #7c3aed 48%, #0ea5e9);
            animation: releaseNewsBlink 0.95s ease-in-out infinite;
        }

        .release-news-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(88, 166, 255, 0.12);
            color: var(--accent);
            font-size: 13px;
        }

        .release-news-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--red);
            box-shadow: 0 0 0 4px rgba(248, 81, 73, 0.12);
        }

        .release-news-button:not(.is-unread) .release-news-dot {
            display: none;
        }

        @keyframes releaseNewsBlink {
            0%,
            100% {
                transform: translateY(0) scale(1);
                filter: saturate(1);
                box-shadow:
                    0 12px 32px rgba(255, 45, 85, 0.35),
                    0 0 0 0 rgba(255, 204, 0, 0.48);
            }

            50% {
                transform: translateY(-1px) scale(1.045);
                filter: saturate(1.4);
                box-shadow:
                    0 16px 42px rgba(124, 58, 237, 0.52),
                    0 0 0 8px rgba(255, 204, 0, 0);
            }
        }

        .release-announcement-panel {
            background: var(--bg-surface);
            border: 1px solid var(--border-2);
            border-radius: 12px;
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.36);
            overflow: hidden;
            max-height: min(720px, calc(100vh - 78px));
            display: flex;
            flex-direction: column;
        }

        .release-announcement-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            padding: 18px 18px 14px;
            border-bottom: 1px solid var(--border);
        }

        .release-announcement-heading {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 0;
        }

        .release-announcement-spark {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(88, 166, 255, 0.12);
            color: var(--accent);
            flex: 0 0 auto;
        }

        .release-announcement-title {
            color: var(--text-1);
            font-size: 15px;
            line-height: 1.3;
            margin-bottom: 3px;
        }

        .release-announcement-subtitle {
            color: var(--text-2);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.03em;
        }

        .release-announcement-close {
            border: 0;
            background: transparent;
            color: var(--text-2);
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            padding: 6px;
        }

        .release-announcement-body {
            overflow-y: auto;
            padding: 14px 18px 18px;
        }

        .release-announcement-card {
            border: 1px solid var(--border);
            border-radius: 10px;
            background: color-mix(in srgb, var(--bg-hover) 55%, transparent);
            padding: 16px;
            margin-bottom: 16px;
        }

        .release-announcement-eyebrow {
            color: var(--accent);
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }

        .release-announcement-description {
            color: var(--text-2);
            font-size: 13px;
            line-height: 1.55;
            margin-top: 8px;
        }

        .release-announcement-section {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-top: 14px;
        }

        .release-announcement-badge {
            flex: 0 0 auto;
            border-radius: 5px;
            border: 1px solid var(--accent-brd);
            background: rgba(88, 166, 255, 0.12);
            color: var(--accent);
            font-size: 10px;
            font-weight: 800;
            padding: 2px 7px;
            text-transform: uppercase;
        }

        .release-announcement-badge.is-green {
            border-color: rgba(63, 185, 80, 0.55);
            background: rgba(63, 185, 80, 0.12);
            color: var(--green);
        }

        .release-announcement-badge.is-purple {
            border-color: rgba(163, 113, 247, 0.55);
            background: rgba(163, 113, 247, 0.12);
            color: var(--purple);
        }

        .release-announcement-badge.is-red {
            border-color: rgba(248, 81, 73, 0.55);
            background: rgba(248, 81, 73, 0.1);
            color: var(--red);
        }

        .release-announcement-list {
            display: grid;
            gap: 8px;
            list-style: none;
            min-width: 0;
        }

        .release-announcement-list li {
            position: relative;
            color: var(--text-1);
            font-size: 13px;
            line-height: 1.45;
            padding-left: 18px;
        }

        .release-announcement-list li::before {
            content: "";
            position: absolute;
            left: 0;
            top: 7px;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--green);
        }

        .release-announcement-refresh {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-2);
            font-size: 12px;
            padding: 10px 12px;
            border: 1px solid var(--border-2);
            border-radius: 7px;
            background: var(--bg-hover);
            margin-top: 14px;
        }

        .release-announcement-refresh kbd {
            color: var(--text-1);
            background: var(--bg-input);
            border: 1px solid var(--border-2);
            border-bottom-width: 2px;
            border-radius: 5px;
            padding: 2px 6px;
            font-family: inherit;
            font-size: 11px;
            font-weight: 700;
        }

        .release-announcement-previous {
            display: grid;
            gap: 9px;
            margin-top: 8px;
        }

        .release-announcement-previous-title {
            color: var(--text-2);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin: 16px 0 8px;
        }

        .release-announcement-version {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: var(--bg-input);
            padding: 11px 12px;
        }

        .release-announcement-version strong {
            color: var(--text-1);
            font-size: 13px;
        }

        .release-announcement-version span {
            color: var(--text-2);
            font-size: 11px;
        }

        .release-announcement-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 18px;
            border-top: 1px solid var(--border);
            background: color-mix(in srgb, var(--bg-hover) 58%, transparent);
        }

        .release-announcement-seen {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--green);
            font-size: 12px;
            font-weight: 700;
        }

        @media (max-width: 900px) {
            .release-news-button {
                top: 62px;
                right: 16px;
            }

            .release-announcement {
                top: 104px;
                right: 16px;
            }
        }

        code {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', monospace;
        }
    </style>
    <script>
        (function() {
            const saved = localStorage.getItem('hh_theme') || 'dark';
            const starsPaused = localStorage.getItem('hh_stars_paused') === '1';
            document.documentElement.setAttribute('data-theme', saved);
            document.documentElement.classList.toggle('stars-paused', starsPaused);
            const link = document.getElementById('hljs-theme');
            if (link) {
                link.href = saved === 'dark' ?
                    'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css' :
                    'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css';
            }
        })();
    </script>
</head>

<body>

    <div class="bg-grid"></div>

    <?php
    $areas        = \OfficeHub\Models\Area::all();
    $activeAreaId = \OfficeHub\Core\Session::get('active_area_id');
    $activeArea   = null;
    foreach ($areas as $a) {
        if ($a['id'] == $activeAreaId) {
            $activeArea = $a;
            break;
        }
    }
    $user = currentUser();
    $showRepos = canUseRepos();
    $showSupport = canUseSupport();
    $showBoard = canUseBoard();
    $notificationsEnabled = canUseNotifications();
    $isSystemAdmin = ($user['role'] ?? '') === 'admin';
    $announcement = file_exists(BASE_PATH . '/config/announcements.php')
        ? require BASE_PATH . '/config/announcements.php'
        : [];
    $announcementEnabled = !empty($announcement['active']) && !empty($announcement['id']);
    $announcementStorageKey = $announcementEnabled
        ? 'hh_announcement_' . (int)($user['id'] ?? 0) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$announcement['id'])
        : '';
    $announcementSections = [];
    if (!empty($announcement['sections']) && is_array($announcement['sections'])) {
        $announcementSections = $announcement['sections'];
    } elseif (!empty($announcement['features']) && is_array($announcement['features'])) {
        $announcementSections = [[
            'label' => 'Nuevo',
            'tone' => 'blue',
            'items' => $announcement['features'],
        ]];
    }
    $announcementPreviousVersions = !empty($announcement['previous_versions']) && is_array($announcement['previous_versions'])
        ? $announcement['previous_versions']
        : [];
    $navRoleParts = [];
    $supportRole = $user['support_role'] ?? 'none';

    if ($isSystemAdmin) {
        $navRoleParts[] = 'admin';
    } else {
        if ($showRepos) {
            $navRoleParts[] = $user['role'] ?? 'repos';
        }

        if ($supportRole === 'support_admin') {
            $navRoleParts[] = 'soporte admin';
        } elseif ($supportRole === 'support_viewer') {
            $navRoleParts[] = 'soporte viewer';
        }
    }

    $navRoleLabel = implode(' / ', $navRoleParts) ?: 'sin acceso';
    $displayName = trim((string)($user['username'] ?? 'Usuario'));
    $initialParts = preg_split('/\s+/', trim((string)preg_replace('/[^a-zA-Z0-9]+/', ' ', $displayName)));
    $userInitials = count($initialParts) >= 2
        ? strtoupper(substr($initialParts[0], 0, 1) . substr($initialParts[1], 0, 1))
        : strtoupper(substr($displayName, 0, 2));
    $userRoleDescription = $isSystemAdmin ? 'Administrador' : $navRoleLabel;
    ?>

    <!-- Navbar -->
    <div class="navbar-wrap">
        <nav class="navbar">

            <a href="<?= $showRepos ? base() : base('soporte') ?>" class="nav-logo">
                <div class="nav-logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                    </svg>
                </div>
                <span class="nav-logo-text">OfficeHub</span>
            </a>

            <div class="nav-divider"></div>

            <?php if ($showRepos): ?>
                <a href="<?= base('repos') ?>" class="nav-pill">Repositorios</a>
            <?php endif; ?>

            <?php if ($showSupport): ?>
                <a href="<?= base('soporte') ?>" class="nav-pill">Documentación</a>
            <?php endif; ?>

            <?php if ($showBoard): ?>
                <a href="<?= base('tablero') ?>" class="nav-pill">Tablero</a>
            <?php endif; ?>

            <!-- Selector de áreas -->
            <?php if ($showRepos): ?>
            <div class="area-dropdown-wrap">
                <div class="area-trigger <?= $activeArea ? 'has-area' : '' ?>"
                    onclick="toggleAreaDropdown()" id="area-trigger">
                    <?php if ($activeArea): ?>
                        <span class="area-dot" style="background:<?= e($activeArea['color']) ?>;"></span>
                        <?= e($activeArea['name']) ?>
                    <?php else: ?>
                        <svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor" style="opacity:0.6">
                            <path d="M1 2.75A.75.75 0 0 1 1.75 2h12.5a.75.75 0 0 1 0 1.5H1.75A.75.75 0 0 1 1 2.75Zm0 5A.75.75 0 0 1 1.75 7h12.5a.75.75 0 0 1 0 1.5H1.75A.75.75 0 0 1 1 7.75ZM1.75 12h12.5a.75.75 0 0 1 0 1.5H1.75a.75.75 0 0 1 0-1.5Z" />
                        </svg>
                        Áreas
                    <?php endif; ?>
                    <svg viewBox="0 0 10 6" width="10" height="6" fill="none" stroke="currentColor" stroke-width="1.5" class="area-chevron" id="area-chevron">
                        <path d="M1 1l4 4 4-4" />
                    </svg>
                </div>

                <div class="area-dropdown" id="area-dropdown">
                    <div class="area-label">Cambiar área</div>

                    <div class="area-option <?= !$activeArea ? 'selected' : '' ?>"
                        onclick="switchArea('all')">
                        <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor" style="color:var(--text-2)">
                            <path d="M1 2.75A.75.75 0 0 1 1.75 2h12.5a.75.75 0 0 1 0 1.5H1.75A.75.75 0 0 1 1 2.75Zm0 5A.75.75 0 0 1 1.75 7h12.5a.75.75 0 0 1 0 1.5H1.75A.75.75 0 0 1 1 7.75ZM1.75 12h12.5a.75.75 0 0 1 0 1.5H1.75a.75.75 0 0 1 0-1.5Z" />
                        </svg>
                        Todas las áreas
                        <?php if (!$activeArea): ?><span class="area-check">✓</span><?php endif; ?>
                    </div>

                    <div class="area-sep"></div>

                    <?php foreach ($areas as $area): ?>
                        <div class="area-option <?= ($activeArea && $activeArea['id'] == $area['id']) ? 'selected' : '' ?>"
                            onclick="switchArea(<?= $area['id'] ?>)">
                            <span class="area-dot" style="background:<?= e($area['color']) ?>;"></span>
                            <?= e($area['name']) ?>
                            <?php if ($activeArea && $activeArea['id'] == $area['id']): ?>
                                <span class="area-check">✓</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($isSystemAdmin): ?>
                        <div class="area-sep"></div>
                        <div class="area-option" onclick="window.location='<?= base('admin/areas') ?>'"
                            style="color:var(--text-2);font-size:12px;">
                            <svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor">
                                <path d="M7.75 2a.75.75 0 0 1 .75.75V7h4.25a.75.75 0 0 1 0 1.5H8.5v4.25a.75.75 0 0 1-1.5 0V8.5H2.75a.75.75 0 0 1 0-1.5H7V2.75A.75.75 0 0 1 7.75 2Z" />
                            </svg>
                            Gestionar áreas
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form oculto para cambiar área -->
            <form id="area-form" method="POST" action="<?= base('area/switch') ?>" style="display:none;">
                <?= csrf_field() ?>
                <input type="hidden" name="area_id" id="area-form-input">
            </form>
            <?php endif; ?>

            <div class="nav-right">
                <?php if ($notificationsEnabled): ?>
                    <div class="nav-notifications" id="notifications-wrap">
                        <button type="button" class="nav-bell" id="notifications-btn" title="Notificaciones" aria-label="Notificaciones">
                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7" />
                                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                            </svg>
                            <span class="nav-bell-count" id="notifications-count">0</span>
                        </button>
                        <div class="notifications-dropdown" id="notifications-dropdown">
                            <div class="notifications-head">
                                <strong>Notificaciones</strong>
                                <div class="notifications-actions">
                                    <button type="button" class="notifications-desktop" id="notifications-desktop">Activar escritorio</button>
                                    <button type="button" id="notifications-read-all">Marcar todas</button>
                                </div>
                            </div>
                            <div id="notifications-list">
                                <div class="notification-empty">Cargando...</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="nav-user-menu-wrap" id="user-menu-wrap">
                    <button type="button" class="nav-user-trigger" id="user-menu-trigger" onclick="toggleUserMenu()" aria-haspopup="true" aria-expanded="false">
                        <span class="nav-avatar"><?= e($userInitials) ?></span>
                        <span class="nav-user-trigger-name"><?= e($displayName) ?></span>
                        <svg class="nav-user-chevron" viewBox="0 0 10 6" width="10" height="6" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M1 1l4 4 4-4" />
                        </svg>
                    </button>

                    <div class="nav-user-menu" id="user-menu">
                        <div class="nav-user-menu-head">
                            <div class="nav-user-menu-name"><?= e($displayName) ?></div>
                            <div class="nav-user-menu-role"><?= e($userRoleDescription) ?></div>
                        </div>

                        <div class="nav-user-menu-sep"></div>

                        <?php if ($isSystemAdmin): ?>
                            <a href="<?= base('admin/users') ?>" class="nav-user-menu-item">
                                <span>Administración</span>
                            </a>
                        <?php endif; ?>

                        <span class="nav-user-menu-item is-disabled" aria-disabled="true">
                            <span>Preferencias</span>
                            <span class="nav-user-menu-muted">Próximamente</span>
                        </span>

                        <button type="button" class="nav-user-menu-item" onclick="toggleTheme(); closeUserMenu();">
                            <span>Cambiar tema</span>
                            <span id="theme-menu-icon"></span>
                        </button>

                        <button type="button" class="nav-user-menu-item" onclick="toggleStars(); closeUserMenu();" id="stars-menu-btn">
                            <span id="stars-menu-label">Pausar estrellas</span>
                            <span id="stars-menu-icon">&#10074;&#10074;</span>
                        </button>

                        <div class="nav-user-menu-sep"></div>

                        <a href="<?= base('logout') ?>" class="nav-user-menu-item is-danger">
                            <span>Cerrar sesión</span>
                        </a>
                    </div>
                </div>
                <button type="button" class="nav-theme" onclick="toggleTheme()" id="theme-btn" title="Cambiar tema">
                    <span id="theme-icon">☀</span>
                </button>
                <button type="button" class="nav-motion" onclick="toggleStars()" id="stars-btn" title="Pausar estrellas" aria-label="Pausar estrellas">
                    <span id="stars-icon">&#10074;&#10074;</span>
                </button>
                <a href="<?= base('logout') ?>" class="nav-logout">Salir</a>
            </div>
        </nav>
    </div>

    <?php if ($announcementEnabled): ?>
        <button type="button" class="release-news-button" id="release-news-button" aria-expanded="false" aria-controls="release-announcement">
            <span class="release-news-icon">&#10024;</span>
            <span>Novedades</span>
            <span class="release-news-dot" aria-hidden="true"></span>
        </button>

        <div class="release-announcement" id="release-announcement" hidden>
            <section class="release-announcement-panel" role="dialog" aria-labelledby="release-announcement-title">
                <header class="release-announcement-head">
                    <div class="release-announcement-heading">
                        <span class="release-announcement-spark">&#10024;</span>
                        <div>
                            <h2 class="release-announcement-title" id="release-announcement-title">Que hay de nuevo</h2>
                            <div class="release-announcement-subtitle"><?= e($announcement['eyebrow'] ?? 'Actualizaciones recientes de OfficeHub') ?></div>
                        </div>
                    </div>
                    <button type="button" class="release-announcement-close" id="release-announcement-close">Cerrar</button>
                </header>

                <div class="release-announcement-body">
                    <article class="release-announcement-card">
                        <div class="release-announcement-eyebrow"><?= e($announcement['version'] ?? 'Version actual') ?></div>
                        <h3 class="release-announcement-title"><?= e($announcement['title'] ?? 'Nueva mejora disponible') ?></h3>
                        <p class="release-announcement-description"><?= e($announcement['description'] ?? '') ?></p>

                        <?php foreach ($announcementSections as $section): ?>
                            <?php
                            $tone = (string)($section['tone'] ?? 'blue');
                            $toneClass = in_array($tone, ['green', 'purple', 'red'], true) ? ' is-' . $tone : '';
                            $items = is_array($section['items'] ?? null) ? $section['items'] : [];
                            ?>
                            <?php if (!empty($items)): ?>
                                <div class="release-announcement-section">
                                    <span class="release-announcement-badge<?= e($toneClass) ?>"><?= e($section['label'] ?? 'Nuevo') ?></span>
                                    <ul class="release-announcement-list">
                                        <?php foreach ($items as $item): ?>
                                            <li><?= e($item) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php if (!empty($announcement['refresh_required'])): ?>
                            <div class="release-announcement-refresh">
                                Para cargar correctamente la mejora, presiona
                                <kbd>Ctrl</kbd>
                                <span>+</span>
                                <kbd>F5</kbd>
                            </div>
                        <?php endif; ?>
                    </article>

                    <?php if (!empty($announcementPreviousVersions)): ?>
                        <div class="release-announcement-previous-title">Versiones anteriores</div>
                        <div class="release-announcement-previous">
                            <?php foreach ($announcementPreviousVersions as $version): ?>
                                <article class="release-announcement-version">
                                    <div>
                                        <span><?= e(($version['version'] ?? '') . (!empty($version['date']) ? ' - ' . $version['date'] : '')) ?></span>
                                        <strong><?= e($version['title'] ?? 'Actualizacion') ?></strong>
                                    </div>
                                    <span><?= e($version['tag'] ?? 'Consultar') ?></span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="release-announcement-foot">
                    <span class="release-announcement-seen" id="release-announcement-seen">&#10003; Leido</span>
                    <button type="button" class="release-announcement-close" id="release-announcement-close-secondary">Cerrar</button>
                </div>
            </section>
        </div>
    <?php endif; ?>

    <!-- Contenido principal -->
    <main style="flex:1;max-width:1380px;margin:0 auto;width:100%;padding:16px 24px 32px;">

        <?php
        $flashError   = \OfficeHub\Core\Session::getFlash('error');
        $flashSuccess = \OfficeHub\Core\Session::getFlash('success');
        ?>
        <?php if ($flashError): ?>
            <div class="flash-error" data-flash data-flash-time="4500"><?= e($flashError) ?></div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="flash-success" data-flash data-flash-time="1400"><?= e($flashSuccess) ?></div>
        <?php endif; ?>

        <?= $content ?>

    </main>

    <!-- Footer -->
    <footer style="border-top:1px solid var(--border);text-align:center;padding:14px;position:relative;z-index:1;">
        <span style="font-size:12px;color:var(--text-3);">OfficeHub — Dpto. de Sistemas &copy; <?= date('Y') ?></span>
    </footer>

    <script>
        const OFFICEHUB_CSRF_TOKEN = <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const OFFICEHUB_NOTIFICATIONS_ENABLED = <?= $notificationsEnabled ? 'true' : 'false' ?>;
        const OFFICEHUB_NOTIFICATIONS_URL = '<?= base('notificaciones') ?>';
        const HH_NOTIFICATIONS_READ_ALL_URL = '<?= base('notificaciones/leer-todas') ?>';
        const HH_DESKTOP_LAST_NOTIFICATION_KEY = 'hh_last_desktop_notification_id';
        const HH_ANNOUNCEMENT_STORAGE_KEY = <?= json_encode($announcementStorageKey, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        let HH_NOTIFICATIONS_LOADING = false;
        let HH_NOTIFICATIONS_BASELINED = false;

        (function setupCsrfProtection() {
            if (!OFFICEHUB_CSRF_TOKEN) return;

            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) return;

                const method = (form.getAttribute('method') || 'GET').toUpperCase();
                if (method !== 'POST' || form.querySelector('input[name="_csrf"]')) return;

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_csrf';
                input.value = OFFICEHUB_CSRF_TOKEN;
                form.appendChild(input);
            }, true);

            const nativeFetch = window.fetch.bind(window);

            window.fetch = (input, init = {}) => {
                const options = { ...init };
                const method = (
                    options.method
                    || (input instanceof Request ? input.method : 'GET')
                    || 'GET'
                ).toUpperCase();

                if (!['GET', 'HEAD', 'OPTIONS'].includes(method)) {
                    const targetUrl = new URL(
                        input instanceof Request ? input.url : String(input),
                        window.location.href
                    );

                    if (targetUrl.origin === window.location.origin) {
                        const headers = new Headers(options.headers || (input instanceof Request ? input.headers : undefined));
                        headers.set('X-CSRF-Token', OFFICEHUB_CSRF_TOKEN);
                        options.headers = headers;

                        if (options.body instanceof URLSearchParams && !options.body.has('_csrf')) {
                            options.body.set('_csrf', OFFICEHUB_CSRF_TOKEN);
                        } else if (options.body instanceof FormData && !options.body.has('_csrf')) {
                            options.body.append('_csrf', OFFICEHUB_CSRF_TOKEN);
                        }
                    }
                }

                return nativeFetch(input, options);
            };
        })();

        function updateThemeIcons(theme) {
            ['theme-icon', 'theme-menu-icon'].forEach((id) => {
                const icon = document.getElementById(id);
                if (icon) {
                    icon.innerHTML = theme === 'dark' ? '&#9728;' : '&#9790;';
                }
            });
        }

        function toggleTheme() {
            const html = document.documentElement;
            const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('hh_theme', next);
            updateThemeIcons(next);
            document.getElementById('theme-icon').textContent = next === 'dark' ? '☀' : '☾';
            document.getElementById('hljs-theme').href = next === 'dark' ?
                'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css' :
                'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css';
            hljs.highlightAll();
        }

        function setupReleaseAnnouncement() {
            const announcement = document.getElementById('release-announcement');
            const openButton = document.getElementById('release-news-button');
            const closeButtons = [
                document.getElementById('release-announcement-close'),
                document.getElementById('release-announcement-close-secondary')
            ].filter(Boolean);
            const seenLabel = document.getElementById('release-announcement-seen');

            if (!announcement || !openButton || !HH_ANNOUNCEMENT_STORAGE_KEY) {
                return;
            }

            const isSeen = () => localStorage.getItem(HH_ANNOUNCEMENT_STORAGE_KEY) === 'seen';

            function syncSeenState() {
                const seen = isSeen();
                openButton.classList.toggle('is-unread', !seen);
                if (seenLabel) {
                    seenLabel.hidden = !seen;
                }
            }

            function markSeen() {
                localStorage.setItem(HH_ANNOUNCEMENT_STORAGE_KEY, 'seen');
                syncSeenState();
            }

            function openNews() {
                announcement.hidden = false;
                openButton.setAttribute('aria-expanded', 'true');
                markSeen();
            }

            function closeNews() {
                announcement.hidden = true;
                openButton.setAttribute('aria-expanded', 'false');
            }

            syncSeenState();

            openButton.addEventListener('click', (event) => {
                event.stopPropagation();
                if (announcement.hidden) {
                    openNews();
                } else {
                    closeNews();
                }
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeNews);
            });

            document.addEventListener('click', (event) => {
                if (announcement.hidden) return;
                if (announcement.contains(event.target) || openButton.contains(event.target)) return;
                closeNews();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !announcement.hidden) {
                    closeNews();
                    openButton.focus();
                }
            });
        }

        function updateStarsControl(paused) {
            const controls = [
                document.getElementById('stars-btn'),
                document.getElementById('stars-menu-btn')
            ].filter(Boolean);
            const icons = [
                document.getElementById('stars-icon'),
                document.getElementById('stars-menu-icon')
            ].filter(Boolean);
            const label = document.getElementById('stars-menu-label');
            const text = paused ? 'Reanudar estrellas' : 'Pausar estrellas';

            controls.forEach((btn) => {
                btn.classList.toggle('is-paused', paused);
                btn.title = text;
                btn.setAttribute('aria-label', text);
            });

            icons.forEach((icon) => {
                icon.innerHTML = paused ? '&#9654;' : '&#10074;&#10074;';
            });

            if (label) {
                label.textContent = text;
            }
        }

        function toggleStars() {
            const html = document.documentElement;
            const paused = !html.classList.contains('stars-paused');

            html.classList.toggle('stars-paused', paused);
            localStorage.setItem('hh_stars_paused', paused ? '1' : '0');
            updateStarsControl(paused);
        }

        function closeUserMenu() {
            const menu = document.getElementById('user-menu');
            const trigger = document.getElementById('user-menu-trigger');

            if (menu) menu.classList.remove('open');
            if (trigger) {
                trigger.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            }
        }

        function toggleUserMenu() {
            const menu = document.getElementById('user-menu');
            const trigger = document.getElementById('user-menu-trigger');

            if (!menu || !trigger) return;

            const open = menu.classList.toggle('open');
            trigger.classList.toggle('is-open', open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        function toggleAreaDropdown() {
            const dd = document.getElementById('area-dropdown');
            const ch = document.getElementById('area-chevron');
            const open = dd.classList.toggle('open');
            ch.classList.toggle('open', open);
        }

        function switchArea(areaId) {
            document.getElementById('area-form-input').value = areaId;
            document.getElementById('area-form').submit();
        }

        document.addEventListener('click', function(e) {
            const wrap = document.querySelector('.area-dropdown-wrap');
            if (wrap && !wrap.contains(e.target)) {
                document.getElementById('area-dropdown').classList.remove('open');
                document.getElementById('area-chevron').classList.remove('open');
            }

            const notificationWrap = document.getElementById('notifications-wrap');
            if (notificationWrap && !notificationWrap.contains(e.target)) {
                closeNotifications();
            }

            const userMenuWrap = document.getElementById('user-menu-wrap');
            if (userMenuWrap && !userMenuWrap.contains(e.target)) {
                closeUserMenu();
            }
        });

        function escapeNotificationText(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function closeNotifications() {
            const dropdown = document.getElementById('notifications-dropdown');
            const button = document.getElementById('notifications-btn');

            if (dropdown) dropdown.classList.remove('open');
            if (button) button.classList.remove('is-open');
        }

        function renderNotifications(items, count) {
            const countEl = document.getElementById('notifications-count');
            const listEl = document.getElementById('notifications-list');

            if (!countEl || !listEl) return;

            countEl.textContent = count > 99 ? '99+' : String(count);
            countEl.classList.toggle('has-items', count > 0);
            showDesktopNotifications(items || []);

            if (!items.length) {
                listEl.innerHTML = '<div class="notification-empty">Sin notificaciones nuevas.</div>';
                return;
            }

            listEl.innerHTML = items.map((item) => `
                <a class="notification-item" href="${escapeNotificationText(item.link || '#')}" data-notification-id="${item.id}">
                    <div class="notification-title">${escapeNotificationText(item.title)}</div>
                    <div class="notification-body">${escapeNotificationText(item.body)}</div>
                    <div class="notification-meta">${escapeNotificationText(item.created_at)}</div>
                </a>
            `).join('');
        }

        function desktopNotificationsSupported() {
            return 'Notification' in window && window.isSecureContext;
        }

        function desktopNotificationsAllowed() {
            return desktopNotificationsSupported() && Notification.permission === 'granted';
        }

        function updateDesktopNotificationsButton() {
            const button = document.getElementById('notifications-desktop');
            if (!button) return;

            button.classList.remove('is-hidden', 'is-active', 'is-blocked');
            button.disabled = false;

            if (!desktopNotificationsSupported()) {
                button.classList.add('is-hidden');
                return;
            }

            if (Notification.permission === 'granted') {
                button.textContent = 'Escritorio activo';
                button.classList.add('is-active');
                button.disabled = true;
                return;
            }

            if (Notification.permission === 'denied') {
                button.textContent = 'Permiso bloqueado';
                button.classList.add('is-blocked');
                button.disabled = true;
                return;
            }

            button.textContent = 'Activar escritorio';
        }

        async function requestDesktopNotifications() {
            if (!desktopNotificationsSupported() || Notification.permission !== 'default') {
                updateDesktopNotificationsButton();
                return;
            }

            await Notification.requestPermission();
            updateDesktopNotificationsButton();
        }

        function notificationId(item) {
            return Number(item && item.id ? item.id : 0);
        }

        function newestNotificationId(items) {
            return items.reduce((newest, item) => Math.max(newest, notificationId(item)), 0);
        }

        function showDesktopNotifications(items) {
            const newestId = newestNotificationId(items);
            const lastShownId = Number(localStorage.getItem(HH_DESKTOP_LAST_NOTIFICATION_KEY) || 0);

            if (!HH_NOTIFICATIONS_BASELINED) {
                if (newestId > lastShownId) {
                    localStorage.setItem(HH_DESKTOP_LAST_NOTIFICATION_KEY, String(newestId));
                }
                HH_NOTIFICATIONS_BASELINED = true;
                return;
            }

            if (!desktopNotificationsAllowed()) {
                if (newestId > lastShownId) {
                    localStorage.setItem(HH_DESKTOP_LAST_NOTIFICATION_KEY, String(newestId));
                }
                return;
            }

            const freshItems = items
                .filter((item) => notificationId(item) > lastShownId)
                .sort((a, b) => notificationId(a) - notificationId(b));

            if (!freshItems.length) {
                return;
            }

            freshItems.slice(-3).forEach((item) => {
                const desktopNotification = new Notification(item.title || 'Nueva notificacion en OfficeHub', {
                    body: item.body || '',
                    tag: `officehub-${notificationId(item)}`,
                    renotify: false
                });

                desktopNotification.onclick = () => {
                    window.focus();
                    if (item.link && item.link !== '#') {
                        window.location.href = item.link;
                    }
                    desktopNotification.close();
                };
            });

            localStorage.setItem(HH_DESKTOP_LAST_NOTIFICATION_KEY, String(newestId));
        }

        async function loadNotifications() {
            if (HH_NOTIFICATIONS_LOADING) return;

            HH_NOTIFICATIONS_LOADING = true;
            try {
                const separator = OFFICEHUB_NOTIFICATIONS_URL.includes('?') ? '&' : '?';
                const response = await fetch(`${OFFICEHUB_NOTIFICATIONS_URL}${separator}_=${Date.now()}`, {
                    headers: {
                        'Accept': 'application/json'
                    },
                    cache: 'no-store'
                });
                const data = await response.json();

                if (!data.ok) {
                    return;
                }

                if (OFFICEHUB_NOTIFICATIONS_ENABLED && data.enabled) {
                    renderNotifications(data.items || [], Number(data.count || 0));
                }
            } catch (error) {
                return;
            } finally {
                HH_NOTIFICATIONS_LOADING = false;
            }
        }

        function markNotificationRead(id) {
            if (!id) return;

            fetch(`<?= base('notificaciones') ?>/${id}/leer`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                keepalive: true
            }).catch(() => {});
        }

        function setupNotifications() {
            if (!OFFICEHUB_NOTIFICATIONS_ENABLED) return;

            const button = document.getElementById('notifications-btn');
            const dropdown = document.getElementById('notifications-dropdown');
            const list = document.getElementById('notifications-list');
            const readAll = document.getElementById('notifications-read-all');
            const desktopButton = document.getElementById('notifications-desktop');

            if (!button || !dropdown || !list || !readAll) return;

            button.addEventListener('click', () => {
                const open = dropdown.classList.toggle('open');
                button.classList.toggle('is-open', open);
                if (open) loadNotifications();
            });

            list.addEventListener('click', (event) => {
                const link = event.target.closest('[data-notification-id]');
                if (link) {
                    markNotificationRead(link.dataset.notificationId);
                }
            });

            readAll.addEventListener('click', async () => {
                await fetch(HH_NOTIFICATIONS_READ_ALL_URL, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    }
                }).catch(() => {});
                renderNotifications([], 0);
                closeNotifications();
            });

            if (desktopButton) {
                desktopButton.addEventListener('click', requestDesktopNotifications);
                updateDesktopNotificationsButton();
            }

        }

        function setupNotificationPolling() {
            loadNotifications();
            window.setInterval(loadNotifications, 8000);
            window.addEventListener('focus', loadNotifications);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    loadNotifications();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const theme = localStorage.getItem('hh_theme') || 'dark';
            const starsPaused = localStorage.getItem('hh_stars_paused') === '1';
            document.documentElement.classList.toggle('stars-paused', starsPaused);
            updateStarsControl(starsPaused);
            updateThemeIcons(theme);
            document.getElementById('theme-icon').textContent = theme === 'dark' ? '☀' : '☾';
            hljs.highlightAll();
            setupNotifications();
            setupNotificationPolling();
            setupReleaseAnnouncement();

            document.querySelectorAll('[data-flash]').forEach((flash) => {
                const delay = Number(flash.dataset.flashTime || 1600);
                window.setTimeout(() => {
                    flash.classList.add('flash-hide');
                    window.setTimeout(() => flash.remove(), 260);
                }, delay);
            });
        });
    </script>

</body>

</html>
