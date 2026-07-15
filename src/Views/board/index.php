<?php
/** @var array $lists */
/** @var array $cards */
/** @var array $cardsByList */
/** @var array $users */
/** @var array $commentsByCard */
/** @var int $currentUserId */
/** @var string $currentUserName */
/** @var string $boardVersion */

$labelColors = ['#3fb950', '#d29922', '#f0883e', '#f85149', '#a371f7', '#58a6ff', '#79c0ff'];
$memberColors = ['#1f6feb', '#238636', '#a371f7', '#db6d28', '#2f81f7', '#d29922', '#bf3989'];
?>

<style>
    .board-page {
        --board-list-bg: rgba(22, 27, 34, 0.94);
        --board-list-border: rgba(139, 148, 158, 0.18);
        --board-card-bg: #222831;
        --board-card-border: #39424f;
        --board-card-hover: #2a323d;
        --board-card-shadow: rgba(0, 0, 0, 0.26);
        --board-muted-card: rgba(22, 27, 34, 0.66);
        --board-progress-bg: rgba(31, 111, 235, 0.18);
        position: relative;
        left: 50%;
        width: calc(100vw - 48px);
        transform: translateX(-50%);
    }

    [data-theme="light"] .board-page {
        --board-list-bg: rgba(226, 232, 240, 0.94);
        --board-list-border: rgba(9, 105, 218, 0.18);
        --board-card-bg: #ffffff;
        --board-card-border: #b8c6d8;
        --board-card-hover: #edf6ff;
        --board-card-shadow: rgba(31, 111, 235, 0.12);
        --board-muted-card: rgba(255, 255, 255, 0.66);
        --board-progress-bg: rgba(9, 105, 218, 0.12);
    }

    .board-shell {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        min-height: calc(100vh - 250px);
        overflow-x: auto;
        overflow-y: visible;
        padding: 4px 0 80px;
        scroll-snap-type: x proximity;
    }

    .board-list {
        flex: 0 0 286px;
        width: 286px;
        min-width: 0;
        max-width: 286px;
        max-height: none;
        background: var(--board-list-bg);
        border: 1px solid var(--board-list-border);
        border-radius: 10px;
        overflow: visible;
        scroll-snap-align: start;
        box-shadow: 0 16px 32px var(--board-card-shadow);
    }

    .board-list.drop-active {
        border-color: var(--accent-brd);
        box-shadow: 0 0 0 2px rgba(88, 166, 255, 0.18), 0 16px 32px var(--board-card-shadow);
    }

    .board-list-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        padding: 13px 14px;
        border-bottom: 1px solid var(--border);
    }

    .board-list-title {
        color: var(--text-1);
        font-size: 14px;
        font-weight: 700;
        line-height: 1.35;
        text-transform: uppercase;
    }

    .board-list-desc {
        color: var(--text-2);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.35;
        margin-top: 3px;
    }

    .board-cards {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-height: 58px;
        max-height: none;
        overflow-y: visible;
        padding: 10px;
    }

    .board-card {
        position: relative;
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
        background: var(--board-card-bg);
        border: 1px solid var(--board-card-border);
        border-radius: 8px;
        padding: 10px;
        cursor: pointer;
        box-shadow: 0 8px 18px var(--board-card-shadow);
        transition: background 0.15s, border-color 0.15s, transform 0.15s;
    }

    .board-card:hover {
        background: var(--board-card-hover);
        border-color: var(--accent-brd);
    }

    .board-card-edit {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 1px solid var(--border-2);
        background: var(--bg-hover);
        color: var(--text-2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        cursor: pointer;
        opacity: 0;
        transform: translateY(-2px);
        transition: opacity 0.15s, transform 0.15s, color 0.15s, border-color 0.15s;
        z-index: 2;
    }

    .board-card:hover .board-card-edit,
    .board-card-edit:focus {
        opacity: 1;
        transform: translateY(0);
    }

    .board-card-edit:hover {
        color: var(--accent);
        border-color: var(--accent-brd);
    }

    .board-card.is-complete {
        border-color: rgba(63, 185, 80, 0.68);
        box-shadow: 0 0 0 1px rgba(63, 185, 80, 0.22), 0 8px 18px var(--board-card-shadow);
    }

    .board-card.is-in-progress:not(.is-complete) {
        border-color: var(--accent-brd);
        box-shadow: 0 0 0 1px rgba(88, 166, 255, 0.2), 0 8px 18px var(--board-card-shadow);
    }

    .board-card.dragging {
        opacity: 0.5;
        transform: rotate(1deg);
        cursor: grabbing;
    }

    .board-card-heading {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 8px;
    }

    .board-card-check {
        flex: 0 0 auto;
        width: 18px;
        height: 18px;
        margin-top: 1px;
        border-radius: 50%;
        border: 1px solid var(--border-2);
        background: var(--bg-hover);
        color: transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s, color 0.15s, transform 0.15s;
    }

    .board-card-check:hover {
        border-color: var(--green);
        transform: scale(1.05);
    }

    .board-card-check.is-complete {
        background: var(--green);
        border-color: var(--green);
        color: #fff;
    }

    .board-card-check:disabled {
        opacity: 0.6;
        cursor: wait;
        transform: none;
    }

    .board-card-check {
        font-size: 0;
    }

    .board-card-check::before {
        content: "\2713";
        color: transparent;
        font-size: 12px;
        line-height: 1;
    }

    .board-card-check.is-complete::before {
        color: #fff;
    }

    .board-card-title {
        color: var(--text-1);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.35;
        min-width: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        overflow-wrap: anywhere;
    }

    .board-card-description {
        color: var(--text-2);
        font-size: 12px;
        line-height: 1.45;
        margin: 2px 0 8px;
        display: block;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .board-card-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: 8px;
    }

    .board-badge-row {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .board-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: 1px solid var(--border-2);
        border-radius: 6px;
        color: var(--text-2);
        font-size: 11px;
        padding: 2px 6px;
        background: var(--bg-hover);
    }

    .board-card-icon {
        width: 28px;
        min-width: 28px;
        height: 26px;
        justify-content: center;
        padding: 0;
        border-radius: 8px;
        font-size: 13px;
        line-height: 1;
    }

    .board-card-icon.has-count {
        width: auto;
        min-width: 34px;
        padding: 0 7px;
        gap: 4px;
        font-size: 12px;
    }

    .board-card-action-form {
        display: inline-flex;
        margin: 0;
    }

    .board-card-comment-count {
        color: var(--accent);
        border-color: var(--accent-brd);
        background: var(--board-progress-bg);
        font-weight: 700;
    }

    .board-badge-row > .board-badge:not(.board-card-icon) {
        display: none;
    }

    .board-card-delete {
        font-size: 0;
    }

    .board-card-delete::before {
        content: "\1F5D1";
        font-size: 13px;
        line-height: 1;
    }

    .board-requirement-badge.is-ok {
        color: var(--green);
        border-color: rgba(63, 185, 80, 0.72);
        background: rgba(63, 185, 80, 0.12);
        font-weight: 700;
    }

    .board-requirement-badge.is-missing {
        color: var(--text-2);
    }

    .board-card-progress {
        min-width: 28px;
        width: 28px;
        height: 26px;
        padding: 0;
        justify-content: center;
        cursor: pointer;
        font-family: inherit;
        font-size: 13px;
        line-height: 1;
        transition: color 0.15s, border-color 0.15s, background 0.15s;
    }

    .board-card-progress:hover {
        color: var(--accent);
        border-color: var(--accent-brd);
    }

    .board-card-progress.is-active {
        color: var(--accent);
        border-color: var(--accent-brd);
        background: var(--board-progress-bg);
        font-weight: 700;
    }

    .board-card-progress:disabled {
        opacity: 0.6;
        cursor: wait;
    }

    .board-members {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
        margin-left: auto;
    }

    .board-member {
        position: relative;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: 1px solid var(--border-2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
    }

    .board-member::after {
        content: "";
        position: absolute;
        right: -1px;
        bottom: -1px;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--green);
        border: 2px solid var(--board-card-bg);
    }

    .board-empty {
        color: var(--text-3);
        font-size: 12px;
        padding: 8px;
        background: var(--board-muted-card);
        border: 1px dashed var(--border-2);
        border-radius: 8px;
    }

    .board-status {
        display: none;
        margin-bottom: 12px;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 13px;
    }

    .board-status.ok {
        display: block;
        position: fixed;
        top: 78px;
        left: 50%;
        z-index: 140;
        width: min(760px, calc(100vw - 36px));
        transform: translateX(-50%);
        box-shadow: 0 18px 44px rgba(0, 0, 0, 0.28);
        color: var(--green);
        background: var(--green-bg);
        border: 1px solid var(--green);
    }

    .board-status.error {
        display: block;
        position: fixed;
        top: 78px;
        left: 50%;
        z-index: 140;
        width: min(760px, calc(100vw - 36px));
        transform: translateX(-50%);
        box-shadow: 0 18px 44px rgba(0, 0, 0, 0.28);
        color: var(--red);
        background: var(--red-bg);
        border: 1px solid var(--red);
    }

    .board-live-notice {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 80;
        display: flex;
        align-items: center;
        gap: 12px;
        max-width: 360px;
        background: var(--bg-surface);
        border: 1px solid var(--accent-brd);
        border-radius: 10px;
        padding: 12px 14px;
        box-shadow: 0 18px 44px rgba(0, 0, 0, 0.3);
        color: var(--text-1);
        font-size: 13px;
    }

    .board-live-notice[hidden] {
        display: none;
    }

    .board-live-notice button {
        border: 1px solid var(--accent-brd);
        background: var(--accent-bg);
        color: #fff;
        border-radius: 7px;
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
    }

    .board-add-card {
        border-top: 1px solid var(--border);
        padding: 10px;
        max-height: none;
        overflow: visible;
    }

    .board-add-card[open] {
        max-height: none;
        overflow-y: visible;
    }

    .board-add-card summary,
    .board-add-list summary {
        color: var(--text-2);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        list-style: none;
    }

    .board-add-card summary::-webkit-details-marker,
    .board-add-list summary::-webkit-details-marker {
        display: none;
    }

    .board-form {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 10px;
    }

    .board-archive-option {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 48px;
        padding: 8px 10px;
        border: 1px solid var(--border-2);
        border-radius: 7px;
        background: var(--bg-input);
        cursor: pointer;
    }

    .board-archive-copy {
        min-width: 0;
    }

    .board-archive-title {
        display: block;
        color: var(--text-1);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.3;
    }

    .board-archive-help {
        display: block;
        margin-top: 2px;
        color: var(--text-3);
        font-size: 10px;
        line-height: 1.35;
    }

    .board-switch {
        position: relative;
        width: 34px;
        height: 20px;
        flex: 0 0 auto;
    }

    .board-switch input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .board-switch-track {
        position: absolute;
        inset: 0;
        border: 1px solid var(--border-2);
        border-radius: 20px;
        background: var(--bg-hover);
        transition: background 0.15s, border-color 0.15s;
    }

    .board-switch-track::after {
        content: "";
        position: absolute;
        top: 3px;
        left: 3px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--text-2);
        transition: transform 0.15s, background 0.15s;
    }

    .board-switch input:checked + .board-switch-track {
        border-color: var(--accent-brd);
        background: var(--accent-bg);
    }

    .board-switch input:checked + .board-switch-track::after {
        transform: translateX(14px);
        background: #fff;
    }

    .board-switch input:focus-visible + .board-switch-track {
        outline: 2px solid var(--accent);
        outline-offset: 2px;
    }

    .board-input,
    .board-textarea {
        width: 100%;
        background: var(--bg-input);
        border: 1px solid var(--border-2);
        color: var(--text-1);
        border-radius: 7px;
        padding: 8px 9px;
        font-size: 12px;
        outline: none;
    }

    .board-textarea {
        min-height: 68px;
        resize: vertical;
    }

    .board-assignee-picker {
        display: flex;
        flex-direction: column;
        gap: 6px;
        max-height: 154px;
        overflow-y: auto;
        background: var(--bg-input);
        border: 1px solid var(--border-2);
        border-radius: 8px;
        padding: 7px;
    }

    .board-add-card .board-assignee-picker {
        max-height: 120px;
    }

    .board-assignee-option {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 34px;
        border: 1px solid transparent;
        border-radius: 7px;
        padding: 6px 7px;
        cursor: pointer;
        color: var(--text-2);
        font-size: 12px;
        transition: background 0.15s, border-color 0.15s, color 0.15s;
    }

    .board-assignee-option:hover {
        background: var(--bg-hover);
        border-color: var(--border-2);
        color: var(--text-1);
    }

    .board-assignee-option input {
        display: none;
    }

    .board-assignee-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--bg-hover);
        border: 1px solid var(--border-2);
        color: var(--text-2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 800;
        flex: 0 0 auto;
    }

    .board-assignee-option input:checked + .board-assignee-avatar {
        background: var(--accent-bg);
        border-color: var(--accent-brd);
        color: #fff;
    }

    .board-assignee-option.is-unassigned .board-assignee-avatar {
        font-size: 14px;
    }

    .board-assignee-option.is-unassigned input:checked + .board-assignee-avatar {
        background: var(--bg-hover);
        border-color: var(--text-3);
        color: var(--text-1);
    }

    .board-assignee-option:has(input:checked) {
        background: rgba(31, 111, 235, 0.14);
        border-color: var(--accent-brd);
        color: var(--text-1);
    }

    .board-assignee-name {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-weight: 600;
    }

    .board-submit {
        background: var(--accent-bg);
        border: 1px solid var(--accent-brd);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        padding: 8px 10px;
        border-radius: 7px;
        cursor: pointer;
    }

    .board-submit:disabled {
    cursor: wait;
    opacity: 0.65;
}

    .board-add-card[open] .board-submit {
        position: static;
        box-shadow: none;
    }

    .board-add-list {
        flex: 0 0 286px;
        color: var(--text-2);
        background: rgba(139, 148, 158, 0.16);
        border: 1px dashed var(--border-2);
        border-radius: 10px;
        padding: 13px 14px;
        font-size: 13px;
        font-weight: 600;
    }

    .board-modal {
        position: fixed;
        inset: 0;
        z-index: 80;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(1, 4, 9, 0.68);
        backdrop-filter: blur(6px);
    }

    .board-modal[hidden] {
        display: none;
    }

    .board-modal-panel {
        width: min(520px, 100%);
        max-height: calc(100vh - 48px);
        overflow: auto;
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.42);
        padding: 16px;
    }

    .board-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .board-modal-title {
        color: var(--text-1);
        font-size: 16px;
        font-weight: 700;
    }

    .board-edit-panel {
        width: min(720px, calc(100vw - 32px));
        padding: 0;
        border-radius: 18px;
        overflow: hidden;
        background: var(--bg-surface);
        box-shadow: 0 32px 92px rgba(0, 0, 0, 0.42);
    }

    .board-edit-panel .board-modal-header {
        margin: 0;
        padding: 22px 26px 18px;
        border-bottom: 1px solid var(--border);
    }

    .board-edit-panel .board-modal-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--text-1);
    }

    .board-edit-panel #board-edit-form {
        margin: 0;
        padding: 22px 26px 26px;
        gap: 14px;
    }

    .board-edit-panel .board-input,
    .board-edit-panel .board-textarea {
        border-radius: 11px;
        padding: 11px 12px;
        font-size: 13px;
        background: var(--bg-input);
    }

    .board-edit-panel .board-textarea {
        min-height: 96px;
    }

    .board-edit-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 58px;
        gap: 10px;
    }

    .board-edit-requirements {
        margin: 0;
        padding: 14px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: rgba(139, 148, 158, 0.08);
    }

    .board-edit-assignee-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .board-edit-assignee-head span:first-child {
        color: var(--text-3);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .board-edit-assignee-head span:last-child {
        color: var(--text-3);
        font-size: 11px;
    }

    .board-edit-panel .board-assignee-picker {
        max-height: 176px;
        border-radius: 12px;
        padding: 8px;
    }

    .board-edit-panel .board-submit {
        align-self: flex-end;
        min-width: 150px;
        border-radius: 10px;
        padding: 10px 16px;
    }

    [data-theme="light"] .board-edit-panel {
        background: #fff;
        border-color: #d7e0ea;
        box-shadow: 0 32px 96px -16px rgba(15, 23, 42, 0.22);
    }

    [data-theme="light"] .board-edit-requirements {
        background: rgba(248, 250, 252, 0.78);
        border-color: #d7e0ea;
    }

    .board-modal-close {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 1px solid var(--border-2);
        background: var(--bg-hover);
        color: var(--text-2);
        cursor: pointer;
        font-size: 18px;
        line-height: 1;
    }

    .board-view-panel {
        width: min(720px, 100%);
        padding: 20px;
    }

    #board-view-modal {
        align-items: flex-start;
        overflow-y: auto;
        padding: 92px 24px 104px;
    }

    #board-view-modal .board-view-panel {
        max-height: none;
        margin-bottom: 28px;
    }

    .board-view-heading {
        min-width: 0;
    }

    .board-view-kicker {
        display: block;
        color: var(--text-3);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 5px;
    }

    .board-view-title {
        font-size: 20px;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .board-view-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-2);
        font-size: 12px;
        margin-bottom: 14px;
    }

    .board-view-label[hidden] {
        display: none;
    }

    .board-view-label-swatch {
        width: 46px;
        height: 8px;
        border-radius: 20px;
        display: inline-block;
        background: var(--accent);
    }

    .board-view-description {
        color: var(--text-1);
        font-size: 14px;
        line-height: 1.65;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        background: var(--bg-hover);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 14px;
        margin: 0 0 14px;
    }

    .board-view-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }

    .board-view-info,
    .board-view-section {
        background: var(--bg-hover);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 12px;
    }

    .board-view-info span,
    .board-view-section-label {
        display: block;
        color: var(--text-3);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
    }

    .board-view-info strong,
    .board-view-status-text {
        color: var(--text-1);
        font-size: 13px;
        font-weight: 700;
    }

    .board-view-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .board-view-action {
        border: 1px solid var(--border-2);
        background: var(--bg-surface);
        color: var(--text-1);
        border-radius: 7px;
        padding: 8px 10px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .board-view-action.is-complete {
        border-color: rgba(63, 185, 80, 0.72);
        color: var(--green);
    }

    .board-view-action.is-active {
        border-color: var(--accent-brd);
        color: var(--accent);
    }

    .board-view-action.is-archive {
    border-color: var(--accent-brd);
    color: var(--accent);
}

    .board-view-action.is-danger {
        color: var(--red);
    }

    .board-view-action:disabled {
        opacity: 0.6;
        cursor: wait;
    }

    .board-view-members {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 10px;
    }

    .board-view-member {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-1);
        font-size: 13px;
        font-weight: 600;
    }

    .board-view-member-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 1px solid var(--accent-brd);
        background: rgba(31, 111, 235, 0.22);
        color: var(--accent);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
    }

    .board-view-empty {
        color: var(--text-2);
        font-size: 13px;
    }

    .board-requirement-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 8px;
    }

    .board-requirement-option,
    .board-requirement-status {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-2);
        font-size: 12px;
        line-height: 1.4;
    }

    .board-requirement-status strong {
        color: var(--text-1);
    }

    .board-requirement-status.is-ok {
        color: var(--green);
    }

    .board-requirement-status.is-missing {
        color: var(--red);
    }

    .board-trace {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 10px;
    }

    .board-trace-item {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 10px;
    }

    .board-trace-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        color: var(--text-3);
        font-size: 11px;
        margin-bottom: 6px;
    }

    .board-trace-meta strong {
        color: var(--text-1);
    }

    .board-trace-body {
        color: var(--text-2);
        font-size: 13px;
        line-height: 1.55;
        white-space: pre-wrap;
    }

    .board-attachment-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }

    .board-attachment-card {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--bg-surface);
        padding: 10px;
        color: var(--text-2);
        font-size: 12px;
        text-decoration: none;
    }

    .board-attachment-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .board-attachment-action {
        border: 1px solid var(--border-2);
        border-radius: 6px;
        background: var(--bg-hover);
        color: var(--accent);
        padding: 5px 8px;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        font-family: inherit;
    }

    .board-attachment-action:hover {
        border-color: var(--accent-brd);
    }

    .board-attachment-card strong {
        color: var(--text-1);
        overflow-wrap: anywhere;
    }

    .board-attachment-card img {
        width: 100%;
        max-height: 160px;
        object-fit: contain;
        border-radius: 6px;
        background: var(--bg-base);
    }

    .board-view-footer {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 14px;
    }

    #board-view-modal {
        align-items: flex-start;
        overflow-y: auto;
        padding: 88px 24px 96px;
        background: rgba(1, 4, 9, 0.62);
        backdrop-filter: blur(8px);
    }

    #board-view-modal .board-view-panel {
        width: min(860px, calc(100vw - 48px));
        max-height: calc(100vh - 210px);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        padding: 0;
        border-radius: 16px;
        background: var(--bg-surface);
        box-shadow: 0 32px 90px rgba(0, 0, 0, 0.34);
    }

    [data-theme="light"] #board-view-modal .board-view-panel,
    [data-theme="light"] .board-comments-chat-panel {
        background: #fff;
        border-color: #e8eef5;
        box-shadow: 0 32px 96px -16px rgba(15, 23, 42, 0.16);
    }

    [data-theme="light"] #board-view-modal .board-modal-header,
    [data-theme="light"] .board-comments-chat-head {
        background: #fff;
        border-color: #edf2f7;
    }

    #board-view-modal .board-modal-header {
        align-items: flex-start;
        margin: 0;
        padding: 28px 30px 22px;
        border-bottom: 1px solid var(--border);
    }

    #board-view-modal .board-modal-close {
        width: 34px;
        height: 34px;
        border: 0;
        background: var(--bg-hover);
        color: var(--text-2);
        font-size: 20px;
        transition: background 0.15s ease, color 0.15s ease, transform 0.15s ease;
    }

    #board-view-modal .board-modal-close:hover {
        color: var(--text-1);
        transform: translateY(-1px);
    }

    .board-view-kicker {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: var(--text-1);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.08em;
    }

    .board-view-title {
        font-size: 24px;
        line-height: 1.16;
        letter-spacing: 0;
    }

    .board-view-body {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 245px;
        gap: 26px;
        flex: 1 1 auto;
        min-height: 0;
        max-height: none;
        overflow: auto;
        padding: 24px 30px 18px;
    }

    .board-view-main {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 24px;
        padding-bottom: 18px;
    }

    .board-view-sidebar {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 18px;
        padding-left: 24px;
        border-left: 1px solid var(--border);
    }

    .board-view-main .board-view-section,
    .board-view-sidebar .board-view-section {
        background: transparent;
        border: 0;
        border-radius: 0;
        padding: 0;
        margin: 0;
    }

    .board-view-sidebar .board-view-section {
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }

    .board-view-sidebar .board-view-section:first-child {
        padding-top: 0;
        border-top: 0;
    }

    .board-view-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }

    .board-view-section-label {
        margin: 0;
        color: var(--text-1);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .board-view-section-count {
        color: var(--text-2);
        font-size: 10px;
        font-weight: 700;
    }

    .board-view-label {
        margin: 0;
    }

    .board-view-label {
        width: fit-content;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: var(--bg-hover);
        padding: 5px 9px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .board-view-label-swatch {
        width: 8px;
        height: 8px;
    }

    .board-view-description {
        margin: 0;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        color: var(--text-2);
        font-size: 14px;
        line-height: 1.7;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .board-view-description-more {
        width: fit-content;
        margin-top: 8px;
        border: 0;
        background: transparent;
        color: var(--accent);
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        padding: 0;
    }

    .board-view-description-more[hidden] {
        display: none;
    }

    .board-requirement-progress {
        height: 3px;
        overflow: hidden;
        border-radius: 999px;
        background: var(--border);
        margin-bottom: 12px;
    }

    .board-requirement-progress span {
        display: block;
        height: 100%;
        width: 0%;
        border-radius: inherit;
        background: var(--text-1);
        transition: width 0.25s ease;
    }

    .board-requirement-progress[hidden] {
        display: none;
    }

    .board-requirement-list {
        gap: 8px;
        margin: 0;
    }

    .board-requirement-status {
        justify-content: space-between;
        gap: 10px;
        padding: 7px 0;
        color: var(--text-2);
        font-size: 12px;
    }

    .board-requirement-main {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .board-requirement-dot {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        flex: 0 0 auto;
        border: 1px solid var(--border-2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: transparent;
        font-size: 10px;
        font-weight: 900;
    }

    .board-requirement-dot.is-ok {
        border-color: var(--text-1);
        background: var(--text-1);
        color: var(--bg-base);
    }

    .board-requirement-status strong {
        color: var(--text-3);
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .board-requirement-status.is-ok strong {
        color: var(--green);
    }

    .board-comments-tools {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--text-3);
        font-size: 10px;
        font-weight: 800;
    }

    .board-comments-chat-open,
    .board-comments-chat-link button {
        border: 0;
        background: transparent;
        color: var(--accent);
        cursor: pointer;
        font-size: 11px;
        font-weight: 800;
        padding: 0;
    }

    .board-comments-chat-open {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: var(--text-3);
        text-transform: none;
    }

    .board-comments-chat-open svg {
        width: 12px;
        height: 12px;
        display: block;
    }

    .board-comments-chat-open:hover,
    .board-comments-chat-link button:hover {
        color: var(--accent);
    }

    .board-trace {
        position: relative;
        display: flex;
        flex-direction: column;
        max-height: 150px;
        overflow-y: auto;
        gap: 12px;
        margin: 0;
        padding: 3px 4px 3px 0;
        border-left: 0;
    }

    .board-trace-item {
        position: relative;
        background: transparent;
        border: 0;
        border-radius: 0;
        padding: 0 0 0 13px;
    }

    .board-trace-item::before {
        content: "";
        position: absolute;
        left: -16px;
        top: 6px;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--accent);
        box-shadow: none;
    }

    .board-trace-item.is-final::before {
        background: var(--green);
    }

    .board-trace-item::after {
        content: "";
        position: absolute;
        left: 0;
        top: 25px;
        width: 7px;
        height: 14px;
        border-left: 1px solid rgba(148, 163, 184, 0.38);
        border-top: 1px solid rgba(148, 163, 184, 0.38);
        border-bottom: 1px solid rgba(148, 163, 184, 0.38);
        border-radius: 7px 0 0 7px;
    }

    .board-trace-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: var(--text-3);
        font-size: 10px;
        line-height: 1.2;
    }

    .board-trace-meta strong {
        color: var(--text-1);
        font-size: 11px;
        font-weight: 900;
    }

    .board-trace-final {
        color: var(--green);
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .board-trace-body {
        margin-top: 5px;
        color: var(--text-2);
        font-size: 11px;
        line-height: 1.45;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .board-trace-reply {
        margin: 6px 0 2px;
        border-left: 2px solid var(--accent);
        padding-left: 7px;
        color: var(--text-3);
        font-size: 10px;
        line-height: 1.35;
    }

    .board-trace-reply strong {
        display: block;
        color: var(--text-2);
        font-size: 10px;
        margin-bottom: 1px;
    }

    .board-trace-actions {
        display: flex;
        justify-content: flex-start;
        margin-top: 5px;
    }

    .board-comments-chat-link {
        margin-top: 10px;
        text-align: center;
    }

    .board-comments-chat-link[hidden] {
        display: none;
    }

    .board-comments-chat-panel {
        width: min(510px, calc(100vw - 32px));
        height: min(720px, calc(100vh - 64px));
        padding: 0;
        display: grid;
        grid-template-rows: auto 1fr auto;
        overflow: hidden;
        border-radius: 12px;
        background: #fff;
    }

    .board-comments-chat-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px 16px;
        border-bottom: 1px solid var(--border);
    }

    .board-comments-chat-head h2 {
        margin: 0;
        color: var(--text-1);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .board-comments-chat-head p {
        margin: 3px 0 0;
        color: var(--text-2);
        font-size: 11px;
    }

    .board-comments-chat-kicker {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .board-comments-chat-kicker::before {
        content: "";
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: var(--green);
    }

    .board-comments-chat-body {
        display: flex;
        flex-direction: column;
        gap: 11px;
        overflow-y: auto;
        padding: 18px 20px;
        background: #fff;
    }

    .board-chat-message {
        display: flex;
        flex-direction: column;
        align-self: flex-start;
        align-items: flex-start;
        width: fit-content;
        max-width: min(82%, 340px);
    }

    .board-chat-message.is-own {
        align-self: flex-end;
        align-items: flex-end;
    }

    .board-chat-meta {
        color: var(--text-3);
        font-size: 10px;
        font-weight: 800;
        margin: 0 0 5px;
    }

    .board-chat-bubble {
        display: inline-flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
        border: 1px solid #d7e0ea;
        border-radius: 14px 14px 14px 4px;
        background: #fff;
        color: #172033;
        font-size: 12px;
        line-height: 1.35;
        padding: 8px 13px;
        width: auto;
        max-width: 100%;
        min-width: 0;
        min-height: 0;
        overflow-wrap: anywhere;
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.03);
    }

    .board-chat-message.is-own .board-chat-bubble {
        border-color: #050814;
        background: #050814;
        color: #fff;
        border-radius: 14px 14px 4px 14px;
    }

    .board-chat-text {
        display: block;
        white-space: pre-wrap;
    }

    .board-chat-reply {
        display: block;
        margin-top: 7px;
        border-left: 2px solid var(--accent);
        padding-left: 7px;
        color: inherit;
        opacity: 0.72;
        font-size: 10px;
    }

    .board-chat-reply strong {
        display: block;
        margin-bottom: 2px;
    }

    .board-chat-message .board-comment-reply {
        margin-top: 5px;
        font-size: 10px;
    }

    .board-comments-chat-form {
        border-top: 1px solid var(--border);
        padding: 12px 14px 14px;
        background: #fff;
    }

    .board-comments-chat-form .board-view-comment-row {
        align-items: center;
        margin: 0 0 10px;
    }

    .board-comments-chat-input {
        display: grid;
        grid-template-columns: 1fr 36px;
        gap: 7px;
        align-items: center;
    }

    .board-comments-chat-input .board-textarea {
        min-height: 42px;
        max-height: 92px;
        resize: vertical;
        background: var(--bg-base);
        border-radius: 10px;
        padding: 10px 12px;
    }

    .board-comments-chat-send {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 0;
        background: #b8bdc7;
        color: #fff;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: background 0.15s, transform 0.15s, opacity 0.15s;
    }

    .board-comments-chat-send:hover {
        background: #969da8;
        transform: translateY(-1px);
    }

    .board-comments-chat-form.has-text .board-comments-chat-send {
        background: #050814;
        color: #fff;
    }

    .board-comments-chat-form.has-text .board-comments-chat-send:hover {
        background: #111827;
    }

    .board-comments-chat-send:disabled {
        cursor: wait;
        opacity: 0.65;
        transform: none;
    }

    .board-comments-chat-send svg {
        width: 17px;
        height: 17px;
        display: block;
        transform: translateX(1px);
    }

    [data-theme="dark"] .board-comments-chat-panel,
    [data-theme="dark"] .board-comments-chat-body,
    [data-theme="dark"] .board-comments-chat-form {
        background: #0d1117;
    }

    [data-theme="dark"] .board-chat-message:not(.is-own) .board-chat-bubble {
        background: #161b22;
        color: #e6edf3;
        border-color: #30363d;
    }

    [data-theme="dark"] .board-chat-message.is-own .board-chat-bubble {
        background: #253041;
        color: #f8fafc;
        border-color: #3b4b61;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.24);
    }

    [data-theme="light"] .board-comments-chat-body,
    [data-theme="light"] .board-comments-chat-form {
        background: #fff;
    }

    [data-theme="light"] .board-chat-message:not(.is-own) .board-chat-bubble {
        background: #fff;
        color: #172033;
        border-color: #d7e0ea;
    }

    [data-theme="light"] .board-comments-chat-send {
        background: #b8bdc7;
    }

    [data-theme="light"] .board-comments-chat-send:hover {
        background: #8f98a8;
    }

    [data-theme="light"] .board-comments-chat-form.has-text .board-comments-chat-send {
        background: #050814;
        color: #fff;
    }

    [data-theme="light"] .board-comments-chat-form.has-text .board-comments-chat-send:hover {
        background: #111827;
    }

    [data-theme="dark"] .board-comments-chat-send {
        background: #6e7681;
        color: #f0f6fc;
    }

    [data-theme="dark"] .board-comments-chat-send:hover {
        background: #8b949e;
    }

    [data-theme="dark"] .board-comments-chat-form.has-text .board-comments-chat-send {
        background: #f8fafc;
        color: #020617;
    }

    [data-theme="dark"] .board-comments-chat-form.has-text .board-comments-chat-send:hover {
        background: #e2e8f0;
    }

    .board-comment-reply {
        border: 0;
        background: transparent;
        color: var(--accent);
        cursor: pointer;
        font-size: 11px;
        font-weight: 800;
        padding: 0;
    }

    .board-reply-context {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
        border: 1px solid var(--accent-brd);
        border-radius: 10px;
        background: rgba(88, 166, 255, 0.1);
        padding: 9px 10px;
        color: var(--text-2);
        font-size: 12px;
        line-height: 1.4;
    }

    .board-reply-context[hidden] {
        display: none;
    }

    .board-reply-context strong {
        color: var(--text-1);
    }

    .board-reply-context button {
        border: 0;
        background: transparent;
        color: var(--text-2);
        cursor: pointer;
        font-weight: 800;
    }

    .board-view-comment-form,
    .board-view-attachment-form {
        margin-top: 14px;
    }

    .board-attachment-subsection {
        display: grid;
        gap: 10px;
        margin-top: 14px;
    }

    .board-attachment-subsection + .board-attachment-subsection {
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }

    .board-attachment-subtitle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: var(--text-1);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .board-attachment-subtitle small {
        color: var(--text-3);
        font-size: 10px;
        font-weight: 700;
        text-transform: none;
        letter-spacing: 0;
    }

    .board-view-comment-form .board-textarea {
        min-height: 62px;
        background: transparent;
    }

    .board-view-comment-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 10px;
    }

    .board-view-comment-row .board-submit {
        width: auto;
        min-width: 104px;
        margin: 0;
        background: var(--bg-hover);
        border: 1px solid var(--border-2);
        color: var(--text-1);
    }

    .board-attachment-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin: 0;
    }

    .board-attachment-card {
        align-items: center;
        text-align: center;
        border-radius: 12px;
        background: transparent;
        padding: 14px;
    }

    .board-attachment-card img {
        width: 46px;
        height: 46px;
        object-fit: cover;
    }

    .board-attachment-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-hover);
        color: var(--text-2);
        border: 1px solid var(--border);
        font-size: 10px;
        font-weight: 800;
    }

    .board-attachment-actions {
        justify-content: center;
    }

    .board-attachment-action {
        background: transparent;
        border: 0;
        padding: 2px 4px;
    }

    .board-view-upload-box {
        display: grid;
        gap: 8px;
        padding: 14px;
        border: 1px dashed var(--border-2);
        border-radius: 12px;
        background: rgba(127, 127, 127, 0.04);
        text-align: center;
    }

    .board-view-upload-box .board-submit {
        margin: 0;
    }

    .board-view-status-list {
        display: grid;
        gap: 6px;
    }

    .board-view-message {
        margin-top: 10px;
        padding: 10px 11px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--bg-hover);
        color: var(--text-2);
        font-size: 12px;
        line-height: 1.45;
    }

    .board-view-message[hidden] {
        display: none;
    }

    .board-view-message.error {
        border-color: rgba(248, 81, 73, 0.55);
        background: var(--red-bg);
        color: var(--red);
    }

    .board-view-message.ok {
        border-color: rgba(63, 185, 80, 0.55);
        background: var(--green-bg);
        color: var(--green);
    }

    .board-view-status-text {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 8px;
        background: var(--bg-hover);
        color: var(--text-1);
    }

    .board-view-status-text::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--accent);
    }

    .board-view-actions {
        display: grid;
        gap: 7px;
        margin-top: 8px;
    }

    .board-view-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 8px;
        padding: 8px 11px;
        background: transparent;
        font-size: 12px;
    }

    .board-view-sidebar .board-view-action {
        justify-content: flex-start;
        width: 100%;
        border: 1px solid var(--border);
        color: var(--text-2);
    }

    .board-view-status-list .board-view-action {
        border-color: transparent;
        background: transparent;
        color: var(--text-2);
    }

    .board-view-status-list .board-view-action::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex: 0 0 auto;
    }

    .board-view-status-list .board-view-action.is-pending {
        color: var(--text-2);
    }

    .board-view-status-list .board-view-action.is-pending::before {
        background: #f2b600;
    }

    .board-view-status-list .board-view-action.is-progress {
        color: var(--text-2);
    }

    .board-view-status-list .board-view-action.is-progress::before {
        background: var(--accent);
    }

    .board-view-status-list .board-view-action.is-complete {
        color: var(--text-2);
    }

    .board-view-status-list .board-view-action.is-complete::before {
        background: var(--green);
    }

    .board-view-status-list .board-view-action.is-selected {
        border-color: #050814;
        background: #050814;
        color: #fff;
        box-shadow: none;
    }

    .board-view-status-list .board-view-action.is-selected:disabled {
        opacity: 1;
        cursor: default;
    }

    [data-theme="light"] .board-view-status-list .board-view-action.is-selected {
        border-color: #050814;
        background: #050814;
        color: #fff;
    }

    [data-theme="light"] .board-view-status-list .board-view-action.is-pending {
        color: var(--text-2);
    }

    #board-view-modal {
        --card-title: #020617;
        --card-body: #475569;
        --card-strong: #1e293b;
        --card-soft: #64748b;
        --card-muted: #94a3b8;
        --card-line: #f1f5f9;
        --card-pill: #f8fafc;
        --card-selected: #050814;
    }

    [data-theme="dark"] #board-view-modal {
        --card-title: #f8fafc;
        --card-body: #cbd5e1;
        --card-strong: #e2e8f0;
        --card-soft: #94a3b8;
        --card-muted: #64748b;
        --card-line: rgba(51, 65, 85, 0.68);
        --card-pill: rgba(22, 27, 34, 0.72);
        --card-selected: #f8fafc;
    }

    #board-view-modal .board-view-kicker,
    #board-view-modal .board-view-section-label,
    #board-view-modal .board-comments-chat-open,
    #board-view-modal .board-view-section-count {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        color: var(--card-muted);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.08em;
    }

    #board-view-modal .board-view-title {
        color: var(--card-title);
        font-weight: 700;
    }

    #board-view-modal .board-modal-header,
    #board-view-modal .board-view-sidebar,
    #board-view-modal .board-view-sidebar .board-view-section,
    #board-view-modal .board-view-footer,
    #board-view-modal .board-view-main .board-view-section:not(:first-child) {
        border-color: var(--card-line);
    }

    #board-view-modal .board-modal-close {
        background: var(--card-pill);
        color: var(--card-muted);
    }

    #board-view-modal .board-modal-close:hover {
        color: var(--card-soft);
    }

    #board-view-modal .board-view-label {
        background: var(--card-pill);
        border-color: var(--card-line);
        color: var(--card-soft);
    }

    #board-view-modal .board-view-description,
    #board-view-modal .board-trace-body {
        color: var(--card-body);
    }

    #board-view-modal .board-view-description-more,
    #board-view-modal .board-comments-chat-link button {
        color: var(--card-muted);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 10px;
        font-weight: 800;
    }

    #board-view-modal .board-view-description-more:hover,
    #board-view-modal .board-comments-chat-open:hover,
    #board-view-modal .board-comments-chat-link button:hover {
        color: var(--card-title);
    }

    #board-view-modal .board-view-empty {
        color: var(--card-muted);
        font-size: 12px;
        font-style: italic;
    }

    #board-view-modal .board-requirement-progress {
        background: var(--card-line);
    }

    #board-view-modal .board-requirement-progress span {
        background: var(--card-title);
    }

    #board-view-modal .board-requirement-status {
        color: var(--card-soft);
    }

    #board-view-modal .board-requirement-status strong {
        color: var(--card-muted);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
    }

    #board-view-modal .board-requirement-status.is-ok {
        color: var(--card-muted);
    }

    #board-view-modal .board-requirement-status.is-ok .board-requirement-main span {
        color: var(--card-muted);
        text-decoration: line-through;
    }

    #board-view-modal .board-requirement-status.is-ok strong {
        color: var(--green);
    }

    #board-view-modal .board-requirement-dot {
        border-color: #cbd5e1;
    }

    #board-view-modal .board-requirement-dot.is-ok {
        background: var(--card-title);
        border-color: var(--card-title);
        color: #fff;
    }

    [data-theme="dark"] #board-view-modal .board-requirement-dot.is-ok {
        color: #020617;
    }

    #board-view-modal .board-trace {
        border-color: var(--card-line);
    }

    #board-view-modal .board-trace-item::before {
        background: var(--card-line);
        border-color: var(--card-line);
    }

    #board-view-modal .board-trace-meta {
        color: var(--card-muted);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 9px;
    }

    #board-view-modal .board-trace-meta strong {
        color: var(--card-strong);
        font-family: inherit;
        font-size: 11px;
    }

    #board-view-modal .board-view-comment-form .board-textarea {
        background: rgba(248, 250, 252, 0.62);
        border-color: var(--card-line);
        color: var(--card-strong);
    }

    #board-view-modal .board-view-comment-form .board-textarea::placeholder {
        color: var(--card-muted);
    }

    [data-theme="dark"] #board-view-modal .board-view-comment-form .board-textarea {
        background: rgba(22, 27, 34, 0.28);
        color: var(--card-strong);
    }

    #board-view-modal .board-view-comment-row .board-requirement-option {
        color: var(--card-soft);
    }

    #board-view-modal .board-view-comment-row .board-submit {
        background: #b8bdc7;
        border-color: #b8bdc7;
        color: #fff;
    }

    #board-view-modal .board-attachment-card {
        border-color: var(--card-line);
        color: var(--card-soft);
    }

    #board-view-modal .board-attachment-card strong {
        color: var(--card-strong);
    }

    #board-view-modal .board-attachment-icon {
        background: var(--card-pill);
        border-color: var(--card-line);
        color: var(--card-soft);
    }

    #board-view-modal .board-attachment-action {
        color: var(--card-soft);
    }

    #board-view-modal .board-attachment-action:hover {
        color: var(--card-title);
    }

    #board-view-modal .board-attachment-delete {
        color: var(--red);
    }

    #board-view-modal .board-attachment-delete:hover {
        color: var(--red);
    }

    #board-view-modal .board-view-upload-box {
        background: rgba(248, 250, 252, 0.3);
        border-color: #e2e8f0;
        color: var(--card-soft);
    }

    [data-theme="dark"] #board-view-modal .board-view-upload-box {
        background: rgba(22, 27, 34, 0.18);
        border-color: var(--card-line);
    }

    #board-view-modal .board-view-status-text {
        background: transparent;
        color: var(--card-strong);
        padding-left: 0;
    }

    #board-view-modal .board-view-status-list .board-view-action {
        color: var(--card-soft);
    }

    #board-view-modal .board-view-status-list .board-view-action.is-selected {
        border-color: var(--card-selected);
        background: var(--card-selected);
        color: #fff;
    }

    [data-theme="dark"] #board-view-modal .board-view-status-list .board-view-action.is-selected {
        color: #020617;
    }

    #board-view-modal .board-view-member-avatar {
        background: var(--card-pill);
        border-color: var(--card-line);
        color: var(--card-soft);
    }

    #board-view-modal .board-view-member-name {
        color: var(--card-strong);
        font-weight: 600;
    }

    #board-view-modal .board-view-member-title {
        color: var(--card-muted);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-weight: 700;
    }

    #board-view-modal .board-view-info strong {
        color: var(--card-strong);
        font-weight: 600;
    }

    .board-view-members {
        margin: 0;
        gap: 10px;
    }

    .board-view-member-avatar {
        width: 24px;
        height: 24px;
        border-color: var(--border);
        background: var(--bg-hover);
        color: var(--text-2);
        font-size: 9px;
    }

    .board-view-member {
        align-items: flex-start;
        gap: 10px;
    }

    .board-view-member-copy {
        display: grid;
        gap: 2px;
    }

    .board-view-member-name {
        color: var(--text-1);
        font-size: 12px;
    }

    .board-view-member-title {
        color: var(--text-3);
        font-size: 9px;
        font-weight: 700;
    }

    .board-assignee-title {
        display: block;
        margin-top: 2px;
        color: var(--text-3);
        font-size: 10px;
        font-weight: 500;
    }

    .board-view-info {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 0;
        background: transparent;
        padding: 0;
    }

    .board-view-info span {
        margin: 0;
    }

    .board-view-info strong {
        color: var(--text-1);
        font-size: 12px;
    }

    #board-view-modal .board-view-footer {
        flex: 0 0 auto;
        margin: 0;
        padding: 24px 30px 44px;
        border-top: 1px solid var(--border);
    }

    #board-view-modal .board-view-footer > div {
        display: flex;
        gap: 8px;
    }

    .board-preview-panel {
        width: min(920px, 100%);
    }

    .board-description-panel {
        width: min(760px, 100%);
    }

    .board-description-body {
        max-height: 68vh;
        overflow: auto;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--bg-hover);
        color: var(--text-1);
        padding: 18px;
        font-size: 14px;
        line-height: 1.7;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .board-preview-body {
        min-height: 260px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--bg-hover);
        overflow: hidden;
    }

    .board-preview-body iframe,
    .board-preview-body img {
        display: block;
        width: 100%;
        min-height: 68vh;
        border: 0;
        object-fit: contain;
        background: var(--bg-base);
    }

    .board-preview-fallback {
        padding: 28px;
        color: var(--text-2);
        font-size: 13px;
        line-height: 1.6;
    }

    .board-confirm-panel {
        width: min(420px, 100%);
        animation: boardConfirmIn 0.18s ease-out;
    }

    .board-confirm-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(88, 166, 255, 0.14);
        border: 1px solid var(--accent-brd);
        color: var(--accent);
        font-size: 18px;
        margin-bottom: 10px;
    }

    .board-confirm-copy {
        color: var(--text-2);
        font-size: 13px;
        line-height: 1.55;
        margin: 8px 0 16px;
    }

    .board-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    @keyframes boardConfirmIn {
        from {
            opacity: 0;
            transform: translateY(10px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @media (max-width: 620px) {
        #board-view-modal {
            padding: 76px 12px 92px;
        }

        #board-view-modal .board-view-panel {
            max-height: none;
        }

        .board-view-grid {
            grid-template-columns: 1fr;
        }

        .board-view-body {
            grid-template-columns: 1fr;
            max-height: none;
            padding: 20px 18px 0;
        }

        .board-view-sidebar {
            padding-left: 0;
            border-left: 0;
            border-top: 1px solid var(--border);
            padding-top: 18px;
        }

        .board-attachment-grid {
            grid-template-columns: 1fr;
        }

        .board-view-footer {
            flex-direction: column;
        }
    }
</style>

<div class="board-page">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:18px;">
        <div>
            <h1 style="color:var(--text-1);font-size:22px;font-weight:700;margin-bottom:6px;">Tablero de tareas</h1>
            <p style="font-size:13px;color:var(--text-2);line-height:1.5;">
                <?= count($lists) ?> listas - <?= count($cards) ?> tarjetas locales
            </p>
        </div>
        <a href="<?= base('tablero/historial') ?>"
           style="display:inline-flex;align-items:center;justify-content:center;min-height:34px;padding:0 14px;border:1px solid var(--border-2);border-radius:6px;background:var(--bg-surface);color:var(--text-1);font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap;">
            Historial
        </a>
    </div>

    <div id="board-status" class="board-status"></div>
    <div id="board-live-notice" class="board-live-notice" hidden>
        <span>Hay cambios nuevos en el tablero.</span>
        <button type="button" data-board-reload>Actualizar</button>
    </div>

    <?php if (empty($lists)): ?>
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:30px;text-align:center;">
            <p style="font-size:14px;color:var(--text-2);">Todavia no hay listas en el tablero.</p>
        </div>
    <?php endif; ?>

    <div class="board-shell" id="board-shell">
        <?php foreach ($lists as $list): ?>
            <?php $listId = (int)$list['id']; ?>
            <?php $listCards = $cardsByList[$listId] ?? []; ?>
            <section class="board-list" data-list-id="<?= $listId ?>">
                <div class="board-list-header">
                    <div>
                        <h3 class="board-list-title"><?= e($list['name']) ?></h3>
                        <?php if (!empty($list['description'])): ?>
                            <div class="board-list-desc"><?= e($list['description']) ?></div>
                        <?php endif; ?>
                    </div>
                    <span class="board-list-count" style="font-size:11px;color:var(--text-3);"><?= count($listCards) ?></span>
                </div>

                <div class="board-cards" data-list-id="<?= $listId ?>">
                    <p class="board-empty" style="<?= empty($listCards) ? '' : 'display:none;' ?>">Sin tarjetas.</p>

                    <?php foreach ($listCards as $card): ?>
                        <?php $cardCompleted = !empty($card['is_complete']); ?>
                        <?php $cardInProgress = !$cardCompleted && !empty($card['is_in_progress']); ?>
                        <?php $members = is_array($card['assignees'] ?? null) ? $card['assignees'] : []; ?>
                        <?php $traceSummary = is_array($card['trace_summary'] ?? null) ? $card['trace_summary'] : []; ?>
                        <?php $hasDocumentation = (int)($traceSummary['completion_attachment_count'] ?? 0) > 0; ?>
                        <?php $hasFinalComment = !empty($traceSummary['has_final_comment']); ?>
                        <?php $commentCount = (int)($traceSummary['comment_count'] ?? 0); ?>
                        <?php $memberIds = implode(',', array_map(fn (array $member): int => (int)$member['id'], $members)); ?>
                        <?php $memberNames = implode('|', array_map(fn (array $member): string => (string)($member['username'] ?? 'Usuario'), $members)); ?>
                        <?php $memberTitles = implode('|', array_map(fn (array $member): string => (string)(($member['display_title'] ?? '') ?: 'Equipo OfficeHub'), $members)); ?>
                        <article class="board-card <?= $cardCompleted ? 'is-complete' : ($cardInProgress ? 'is-in-progress' : '') ?>"
                            draggable="true"
                            data-card-id="<?= (int)$card['id'] ?>"
                            data-list-id="<?= $listId ?>"
                            data-card-pos="<?= e($card['position_value'] ?? '') ?>"
                            data-card-complete="<?= $cardCompleted ? '1' : '0' ?>"
                            data-card-progress="<?= $cardInProgress ? '1' : '0' ?>"
                            data-card-title="<?= e($card['title'] ?? '') ?>"
                            data-card-description="<?= e($card['description'] ?? '') ?>"
                            data-card-label-text="<?= e($card['label_text'] ?? '') ?>"
                            data-card-label-color="<?= e($card['label_color'] ?? '') ?>"
                            data-card-due-date="<?= e($card['due_date'] ?? '') ?>"
                            data-card-auto-archive="<?= !empty($card['auto_archive_on_complete']) ? '1' : '0' ?>"
                             data-card-requires-documentation="<?= !empty($card['requires_documentation']) ? '1' : '0' ?>"
                             data-card-requires-final-comment="<?= !empty($card['requires_final_comment']) ? '1' : '0' ?>"
                             data-card-has-documentation="<?= $hasDocumentation ? '1' : '0' ?>"
                             data-card-has-final-comment="<?= $hasFinalComment ? '1' : '0' ?>"
                             data-card-comment-count="<?= $commentCount ?>"
                             data-card-assignees="<?= e($memberIds) ?>"
                             data-card-assignee-names="<?= e($memberNames) ?>"
                             data-card-assignee-titles="<?= e($memberTitles) ?>">

                            <button type="button" class="board-card-edit" title="Editar tarjeta" aria-label="Editar tarjeta">
                                &#9998;
                            </button>

                            <?php if (!empty($card['label_text']) || !empty($card['label_color'])): ?>
                                <div style="display:flex;gap:5px;align-items:center;flex-wrap:wrap;margin-bottom:8px;">
                                    <span title="<?= e($card['label_text'] ?? '') ?>"
                                        style="width:38px;height:7px;border-radius:20px;background:<?= e($card['label_color'] ?: '#58a6ff') ?>;display:inline-block;"></span>
                                    <?php if (!empty($card['label_text'])): ?>
                                        <span style="font-size:11px;color:var(--text-2);"><?= e($card['label_text']) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="board-card-heading">
                                <button type="button"
                                    class="board-card-check <?= $cardCompleted ? 'is-complete' : '' ?>"
                                    aria-pressed="<?= $cardCompleted ? 'true' : 'false' ?>"
                                    title="<?= $cardCompleted ? 'Desmarcar tarjeta' : 'Marcar tarjeta' ?>">
                                    ✓
                                </button>
                                <div class="board-card-title">
                                    <?= e(($card['title'] ?? '') !== '' ? $card['title'] : 'Sin titulo') ?>
                                </div>
                            </div>

                            <?php if (!empty($card['description'])): ?>
                                <p class="board-card-description"><?= e($card['description']) ?></p>
                            <?php endif; ?>

                            <div class="board-card-meta">
                                <div class="board-badge-row">
                                    <button type="button"
                                        class="board-badge board-card-icon board-card-progress <?= $cardInProgress ? 'is-active' : '' ?>"
                                        aria-pressed="<?= $cardInProgress ? 'true' : 'false' ?>"
                                        aria-label="<?= $cardInProgress ? 'Volver a pendiente' : 'Marcar en proceso' ?>"
                                        title="<?= $cardInProgress ? 'Volver a pendiente' : 'Marcar en proceso' ?>">
                                        <?= $cardInProgress ? '&#128309;' : '&#128640;' ?>
                                    </button>

                                    <?php if (!empty($card['due_date'])): ?>
                                        <span class="board-badge board-card-icon" title="Vence: <?= dateFormat($card['due_date'], 'd/m/Y') ?>">&#128197;</span>
                                        <span class="board-badge">○ <?= dateFormat($card['due_date'], 'd/m/Y') ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($card['description'])): ?>
                                        <span class="board-badge board-card-icon" title="Tiene descripcion">&#128221;</span>
                                        <span class="board-badge">≡</span>
                                    <?php endif; ?>

                                    <?php if (empty($card['auto_archive_on_complete'])): ?>
                                        <span class="board-badge board-card-icon" title="Esta tarjeta no se archiva automaticamente">&#128204;</span>
                                    <?php endif; ?>

                                     <?php if (!empty($card['requires_documentation'])): ?>
                                        <span class="board-badge board-card-icon board-requirement-badge board-card-requirement-doc <?= $hasDocumentation ? 'is-ok' : 'is-missing' ?>" title="<?= $hasDocumentation ? 'Documentacion entregada' : 'Requiere documentacion para completarse' ?>">&#128196;<?= $hasDocumentation ? '&#9989;' : '' ?></span>
                                     <?php endif; ?>

                                     <?php if (!empty($card['requires_final_comment'])): ?>
                                        <span class="board-badge board-card-icon board-requirement-badge board-card-requirement-final <?= $hasFinalComment ? 'is-ok' : 'is-missing' ?>" title="<?= $hasFinalComment ? 'Comentario final cargado' : 'Requiere comentario final para completarse' ?>">&#128172;<?= $hasFinalComment ? '&#9989;' : '' ?></span>
                                     <?php endif; ?>

                                    <?php if ($commentCount > 0): ?>
                                        <span class="board-badge board-card-icon board-card-comment-count has-count" title="<?= $commentCount === 1 ? '1 comentario' : $commentCount . ' comentarios' ?>">&#128172;<span><?= $commentCount ?></span></span>
                                    <?php endif; ?>

                                    <form method="POST"
                                        action="<?= base('tablero/cards/' . (int)$card['id'] . '/delete') ?>"
                                        class="board-card-action-form"
                                        data-board-confirm
                                        data-confirm-title="Eliminar tarjeta"
                                        data-confirm-message="Esta accion quita la tarjeta del tablero. Podras revisar registros archivados si aplica."
                                        data-confirm-action="Eliminar">
                                        <button type="submit"
                                            class="board-badge board-card-icon board-card-delete"
                                            style="cursor:pointer;color:var(--red);"
                                            title="Eliminar tarjeta">
                                            ×
                                        </button>
                                    </form>
                                </div>

                                <?php if (!empty($members)): ?>
                                    <div class="board-members">
                                        <?php foreach (array_slice($members, 0, 4) as $index => $member): ?>
                                            <?php
                                            $memberName = $member['username'] ?? 'Usuario';
                                            $memberTitle = (string)(($member['display_title'] ?? '') ?: 'Equipo OfficeHub');
                                            $initials = strtoupper(substr((string)$memberName, 0, 2));
                                            $memberColor = $memberColors[$index % count($memberColors)];
                                            ?>
                                            <span class="board-member" title="<?= e($memberName . ' - ' . $memberTitle) ?>" style="background:<?= e($memberColor) ?>;">
                                                <?= e($initials) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <details class="board-add-card">
                    <summary>+ Anadir tarjeta</summary>
                    <form method="POST" action="<?= base('tablero/cards') ?>" class="board-form">
                        <input type="hidden" name="list_id" value="<?= $listId ?>">
                        <input type="hidden" name="without_assignee" value="0" data-without-assignee>
                        <input class="board-input" type="text" name="title" placeholder="Titulo de la tarea">
                        <textarea class="board-textarea" name="description" placeholder="Descripcion breve"></textarea>
                        <input class="board-input" type="text" name="label_text" placeholder="Etiqueta, ej: urgente">
                        <div style="display:grid;grid-template-columns:1fr 46px;gap:8px;">
                            <input class="board-input" type="date" name="due_date">
                            <input class="board-input" type="color" name="label_color" value="<?= e($labelColors[$listId % count($labelColors)]) ?>" title="Color de etiqueta">
                        </div>
                        <label class="board-archive-option">
                            <span class="board-archive-copy">
                                <span class="board-archive-title">Archivar automaticamente</span>
                                <span class="board-archive-help">La tarjeta saldra del tablero 7 dias despues de completarse.</span>
                            </span>
                            <span class="board-switch">
                                <input type="checkbox" name="auto_archive_on_complete" value="1" checked>
                                <span class="board-switch-track"></span>
                            </span>
                        </label>
                        <div class="board-view-section" style="padding:10px;margin:0;">
                            <span class="board-view-section-label">Requisitos para completar</span>
                            <div class="board-requirement-list">
                                <label class="board-requirement-option">
                                    <input type="checkbox" name="requires_documentation" value="1">
                                    <span>Debe adjuntar documentacion</span>
                                </label>
                                <label class="board-requirement-option">
                                    <input type="checkbox" name="requires_final_comment" value="1">
                                    <span>Debe dejar comentario final</span>
                                </label>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                            <span style="font-size:11px;color:var(--text-3);font-weight:700;text-transform:uppercase;letter-spacing:0.05em;">Asignar a</span>
                            <span style="font-size:11px;color:var(--text-3);">pueden ser varios</span>
                        </div>
                        <div class="board-assignee-picker">
                            <label class="board-assignee-option is-unassigned">
                                <input type="checkbox" data-unassigned-toggle>
                                <span class="board-assignee-avatar">-</span>
                                <span class="board-assignee-name">Sin asignar</span>
                            </label>
                            <?php foreach ($users as $user): ?>
                                <?php
                                $username = (string)($user['username'] ?? 'usuario');
                                $displayTitle = (string)(($user['display_title'] ?? '') ?: 'Equipo OfficeHub');
                                $initials = strtoupper(substr($username, 0, 2));
                                ?>
                                <label class="board-assignee-option">
                                    <input type="checkbox" name="assignees[]" value="<?= (int)$user['id'] ?>">
                                    <span class="board-assignee-avatar"><?= e($initials) ?></span>
                                    <span class="board-assignee-name">
                                        <?= e($username) ?>
                                        <span class="board-assignee-title"><?= e($displayTitle) ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <span style="font-size:11px;color:var(--text-3);line-height:1.4;">Si no elegis asignados, la tarjeta queda sin responsable.</span>
                        <button type="submit" class="board-submit">Crear tarjeta</button>
                    </form>
                </details>
            </section>
        <?php endforeach; ?>

        <details class="board-add-list">
            <summary>+ Anadir otra lista</summary>
            <form method="POST" action="<?= base('tablero/lists') ?>" class="board-form">
                <input class="board-input" type="text" name="name" placeholder="Nombre de la lista" required>
                <input class="board-input" type="text" name="description" placeholder="Descripcion o mail del area">
                <input class="board-input" type="color" name="color" value="#58a6ff" title="Color de lista">
                <button type="submit" class="board-submit">Crear lista</button>
            </form>
        </details>
    </div>
</div>

<div id="board-view-modal" class="board-modal" hidden>
    <div class="board-modal-panel board-view-panel">
        <div class="board-modal-header">
            <div class="board-view-heading">
                <span class="board-view-kicker" id="board-view-list">Tablero</span>
                <h2 class="board-modal-title board-view-title" id="board-view-title">Tarjeta</h2>
            </div>
            <button type="button" class="board-modal-close" data-view-close aria-label="Cerrar">&times;</button>
        </div>

        <div class="board-view-body">
            <div class="board-view-main">
                <div class="board-view-label" id="board-view-label" hidden>
                    <span class="board-view-label-swatch" id="board-view-label-swatch"></span>
                    <span id="board-view-label-text">Etiqueta</span>
                </div>

                <section class="board-view-section">
                    <div class="board-view-section-head">
                        <span class="board-view-section-label">Descripcion</span>
                    </div>
                    <p class="board-view-description" id="board-view-description"></p>
                    <button type="button" class="board-view-description-more" id="board-view-description-more" hidden>
                        Leer mas
                    </button>
                </section>

                <section class="board-view-section">
                    <div class="board-view-section-head">
                        <span class="board-view-section-label">Requisitos de cierre</span>
                        <strong class="board-view-section-count" id="board-view-requirements-summary">0/0 completados</strong>
                    </div>
                    <div class="board-requirement-progress" id="board-view-requirements-progress" hidden>
                        <span></span>
                    </div>
                    <div class="board-requirement-list" id="board-view-requirements">
                        <span class="board-view-empty">Esta tarjeta no tiene requisitos especiales.</span>
                    </div>
                </section>

                <section class="board-view-section">
                    <div class="board-view-section-head">
                        <span class="board-view-section-label">Comentarios</span>
                        <span class="board-comments-tools">
                            <button type="button" class="board-comments-chat-open" id="board-comments-chat-open" hidden>
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M5 6.5h14M5 11.5h10M5 16.5h7M4.5 4.5A2.5 2.5 0 0 0 2 7v7.5A2.5 2.5 0 0 0 4.5 17H7v3l4-3h8.5A2.5 2.5 0 0 0 22 14.5V7a2.5 2.5 0 0 0-2.5-2.5h-15Z"/>
                                </svg>
                                <span>Modo Chat</span>
                            </button>
                            <strong class="board-view-section-count" id="board-view-comments-count">0</strong>
                        </span>
                    </div>
                    <div class="board-trace" id="board-view-comments">
                        <span class="board-view-empty">Todavia no hay comentarios.</span>
                    </div>
                    <div class="board-comments-chat-link" id="board-comments-chat-link" hidden>
                        <button type="button">
                            Ver todos los comentarios en modo chat (<span id="board-comments-chat-total">0</span>)
                        </button>
                    </div>
                    <form method="POST" action="" class="board-form board-view-comment-form" id="board-view-comment-form">
                        <input type="hidden" name="reply_to_comment_id" value="">
                        <div class="board-reply-context" id="board-reply-context" hidden>
                            <span id="board-reply-context-text"></span>
                            <button type="button" id="board-reply-cancel">Cancelar</button>
                        </div>
                        <textarea class="board-textarea" name="body" placeholder="Agrega una nota o comentario de avance..." required></textarea>
                        <div class="board-view-comment-row">
                            <label class="board-requirement-option">
                                <input type="checkbox" name="is_final" value="1">
                                <span>Marcar comentario final de cierre</span>
                            </label>
                            <button type="submit" class="board-submit">Comentar</button>
                        </div>
                    </form>
                </section>

                <section class="board-view-section">
                    <div class="board-view-section-head">
                        <span class="board-view-section-label">Adjuntos y documentos</span>
                    </div>

                    <div class="board-attachment-subsection">
                        <div class="board-attachment-subtitle">
                            <span>Documentacion de trabajo</span>
                            <small>No cuenta como entregable final</small>
                        </div>
                        <div class="board-attachment-grid" id="board-view-work-attachments">
                            <span class="board-view-empty">Todavia no hay documentacion de trabajo.</span>
                        </div>
                        <form method="POST" action="" enctype="multipart/form-data" class="board-form board-view-attachment-form" data-attachment-purpose="work">
                            <input type="hidden" name="attachment_purpose" value="work">
                            <div class="board-view-upload-box">
                                <input class="board-input" type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.gif,.txt,.csv,.log">
                                <span style="font-size:11px;color:var(--text-3);line-height:1.4;">Material inicial, referencias o archivos de apoyo. No habilita completar la tarea.</span>
                                <button type="submit" class="board-submit">Subir documentacion de trabajo</button>
                            </div>
                        </form>
                    </div>

                    <div class="board-attachment-subsection">
                        <div class="board-attachment-subtitle">
                            <span>Entregable de cierre</span>
                            <small>Cuenta para completar la tarjeta</small>
                        </div>
                        <div class="board-attachment-grid" id="board-view-completion-attachments">
                            <span class="board-view-empty">Todavia no hay entregables de cierre.</span>
                        </div>
                        <form method="POST" action="" enctype="multipart/form-data" class="board-form board-view-attachment-form" data-attachment-purpose="completion">
                            <input type="hidden" name="attachment_purpose" value="completion">
                            <div class="board-view-upload-box">
                                <input class="board-input" type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.gif,.txt,.csv,.log">
                                <span style="font-size:11px;color:var(--text-3);line-height:1.4;">Documentacion final, evidencia o reporte requerido para cerrar la tarea.</span>
                                <button type="submit" class="board-submit">Subir entregable de cierre</button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>

            <aside class="board-view-sidebar">
                <section class="board-view-section">
                    <span class="board-view-section-label">Estado</span>
                    <strong class="board-view-status-text" id="board-view-status-text">Pendiente</strong>
                    <div class="board-view-actions board-view-status-list">
                        <button type="button" class="board-view-action is-pending" id="board-view-pending">Pendiente</button>
                        <button type="button" class="board-view-action is-progress" id="board-view-progress">En proceso</button>
                        <button type="button" class="board-view-action is-complete" id="board-view-complete">Marcar completa</button>
                    </div>
                    <div class="board-view-message" id="board-view-status-message" hidden></div>
                </section>

                <section class="board-view-section">
                    <span class="board-view-section-label">Asignacion</span>
                    <div class="board-view-members" id="board-view-members"></div>
                </section>

                <section class="board-view-section">
                    <span class="board-view-section-label">Plazo de entrega</span>
                    <div class="board-view-info">
                        <strong id="board-view-due">Sin fecha</strong>
                    </div>
                </section>

                <section class="board-view-section">
                    <span class="board-view-section-label">Criterio de archivado</span>
                    <div class="board-view-info">
                        <strong id="board-view-archive">Automatico</strong>
                    </div>
                </section>
            </aside>
        </div>

        <div class="board-view-footer">
            <button type="button" class="board-view-action" id="board-view-edit">
                Editar detalles
            </button>

            <div>
                <form
                    method="POST"
                    action=""
                    id="board-view-archive-form"
                    hidden
                    data-board-confirm
                    data-confirm-title="Archivar tarjeta"
                    data-confirm-message="La tarjeta saldra del tablero y quedara guardada en el historial de completadas."
                    data-confirm-action="Archivar"
                >
                    <button type="submit" class="board-view-action is-archive">
                        Archivar
                    </button>
                </form>

                <form
                    method="POST"
                    action=""
                    id="board-view-delete-form"
                    data-board-confirm
                    data-confirm-title="Eliminar tarjeta"
                    data-confirm-message="Esta accion quita la tarjeta del tablero."
                    data-confirm-action="Eliminar"
                >
                    <button type="submit" class="board-view-action is-danger">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="board-description-modal" class="board-modal" hidden>
    <div class="board-modal-panel board-description-panel">
        <div class="board-modal-header">
            <h2 class="board-modal-title" id="board-description-title">Descripcion completa</h2>
            <button type="button" class="board-modal-close" data-description-close aria-label="Cerrar">&times;</button>
        </div>
        <div class="board-description-body" id="board-description-body"></div>
    </div>
</div>

<div id="board-comments-chat-modal" class="board-modal" hidden>
    <div class="board-modal-panel board-comments-chat-panel">
        <div class="board-comments-chat-head">
            <div>
                <div class="board-comments-chat-kicker">
                    <h2>Chat de comentarios</h2>
                </div>
                <p id="board-comments-chat-title">Tarjeta</p>
            </div>
            <button type="button" class="board-modal-close" data-comments-chat-close aria-label="Cerrar">&times;</button>
        </div>

        <div class="board-comments-chat-body" id="board-comments-chat-body">
            <span class="board-view-empty">Todavia no hay comentarios.</span>
        </div>

        <form method="POST" action="" class="board-form board-comments-chat-form" id="board-comments-chat-form">
            <input type="hidden" name="reply_to_comment_id" value="">
            <div class="board-reply-context" id="board-chat-reply-context" hidden>
                <span id="board-chat-reply-context-text"></span>
                <button type="button" id="board-chat-reply-cancel">Cancelar</button>
            </div>
            <div class="board-view-comment-row">
                <label class="board-requirement-option">
                    <input type="checkbox" name="is_final" value="1">
                    <span>Marcar comentario final de cierre</span>
                </label>
            </div>
            <div class="board-comments-chat-input">
                <textarea class="board-textarea" name="body" placeholder="Escribe un mensaje de avance..." required></textarea>
                <button type="submit" class="board-comments-chat-send" aria-label="Enviar comentario">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path fill="currentColor" d="M3.4 20.3 21.2 12 3.4 3.7 3 10.1l10.8 1.9L3 13.9l.4 6.4Z"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<div id="board-edit-modal" class="board-modal" hidden>
    <div class="board-modal-panel board-edit-panel">
        <div class="board-modal-header">
            <h2 class="board-modal-title">Editar tarjeta</h2>
            <button type="button" class="board-modal-close" data-edit-close aria-label="Cerrar">&times;</button>
        </div>

        <form method="POST" action="" class="board-form" id="board-edit-form">
            <input type="hidden" name="without_assignee" value="0" data-without-assignee>
            <input class="board-input" type="text" name="title" placeholder="Titulo de la tarea">
            <textarea class="board-textarea" name="description" placeholder="Descripcion breve"></textarea>
            <input class="board-input" type="text" name="label_text" placeholder="Etiqueta, ej: urgente">
            <div class="board-edit-grid">
                <input class="board-input" type="date" name="due_date">
                <input class="board-input" type="color" name="label_color" value="#58a6ff" title="Color de etiqueta">
            </div>
            <label class="board-archive-option">
                <span class="board-archive-copy">
                    <span class="board-archive-title">Archivar automaticamente</span>
                    <span class="board-archive-help">Desactivalo para tareas recurrentes que deben permanecer en el tablero.</span>
                </span>
                <span class="board-switch">
                    <input type="checkbox" name="auto_archive_on_complete" value="1">
                    <span class="board-switch-track"></span>
                </span>
            </label>
            <div class="board-view-section board-edit-requirements">
                <span class="board-view-section-label">Requisitos para completar</span>
                <div class="board-requirement-list">
                    <label class="board-requirement-option">
                        <input type="checkbox" name="requires_documentation" value="1">
                        <span>Debe adjuntar documentacion</span>
                    </label>
                    <label class="board-requirement-option">
                        <input type="checkbox" name="requires_final_comment" value="1">
                        <span>Debe dejar comentario final</span>
                    </label>
                </div>
            </div>
            <div class="board-edit-assignee-head">
                <span>Asignar a</span>
                <span>pueden ser varios</span>
            </div>
            <div class="board-assignee-picker">
                <label class="board-assignee-option is-unassigned">
                    <input type="checkbox" data-unassigned-toggle>
                    <span class="board-assignee-avatar">-</span>
                    <span class="board-assignee-name">Sin asignar</span>
                </label>
                <?php foreach ($users as $user): ?>
                    <?php
                    $username = (string)($user['username'] ?? 'usuario');
                    $displayTitle = (string)(($user['display_title'] ?? '') ?: 'Equipo OfficeHub');
                    $initials = strtoupper(substr($username, 0, 2));
                    ?>
                    <label class="board-assignee-option">
                        <input type="checkbox" name="assignees[]" value="<?= (int)$user['id'] ?>">
                        <span class="board-assignee-avatar"><?= e($initials) ?></span>
                        <span class="board-assignee-name">
                            <?= e($username) ?>
                            <span class="board-assignee-title"><?= e($displayTitle) ?></span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="board-submit">Guardar cambios</button>
        </form>
    </div>
</div>

<div id="board-attachment-modal" class="board-modal" hidden>
    <div class="board-modal-panel board-preview-panel">
        <div class="board-modal-header">
            <h2 class="board-modal-title" id="board-preview-title">Vista previa</h2>
            <button type="button" class="board-modal-close" data-preview-close aria-label="Cerrar">&times;</button>
        </div>
        <div class="board-preview-body" id="board-preview-body"></div>
        <div class="board-view-footer">
            <span></span>
            <a href="#" class="board-view-action" id="board-preview-download">Descargar</a>
        </div>
    </div>
</div>

<div id="board-confirm-modal" class="board-modal" hidden>
    <div class="board-modal-panel board-confirm-panel">
        <span class="board-confirm-icon">!</span>
        <h2 class="board-modal-title" id="board-confirm-title">Confirmar accion</h2>
        <p class="board-confirm-copy" id="board-confirm-message">Confirma para continuar.</p>
        <div class="board-confirm-actions">
            <button type="button" class="board-view-action" data-confirm-cancel>Cancelar</button>
            <button type="button" class="board-view-action is-danger" data-confirm-accept>Confirmar</button>
        </div>
    </div>
</div>

<script>
    (() => {
        const moveUrl = '<?= base('tablero/cards/move') ?>';
        const completeUrl = '<?= base('tablero/cards/complete') ?>';
        const progressUrl = '<?= base('tablero/cards/progress') ?>';
        const versionUrl = '<?= base('tablero/version') ?>';
        const updateUrlTemplate = '<?= base('tablero/cards/__ID__/update') ?>';
        const archiveUrlTemplate = '<?= base('tablero/cards/__ID__/archive') ?>';
        const deleteUrlTemplate = '<?= base('tablero/cards/__ID__/delete') ?>';
        const traceUrlTemplate = '<?= base('tablero/cards/__ID__/trace') ?>';
        const commentUrlTemplate = '<?= base('tablero/cards/__ID__/comments') ?>';
        const attachmentUrlTemplate = '<?= base('tablero/cards/__ID__/attachments') ?>';
        const status = document.getElementById('board-status');
        const liveNotice = document.getElementById('board-live-notice');
        const liveReloadButton = liveNotice ? liveNotice.querySelector('[data-board-reload]') : null;
        const viewModal = document.getElementById('board-view-modal');
        const viewTitle = document.getElementById('board-view-title');
        const viewList = document.getElementById('board-view-list');
        const viewLabel = document.getElementById('board-view-label');
        const viewLabelSwatch = document.getElementById('board-view-label-swatch');
        const viewLabelText = document.getElementById('board-view-label-text');
        const viewDescription = document.getElementById('board-view-description');
        const viewDescriptionMore = document.getElementById('board-view-description-more');
        const viewDue = document.getElementById('board-view-due');
        const viewArchive = document.getElementById('board-view-archive');
        const viewStatusText = document.getElementById('board-view-status-text');
        const viewMembers = document.getElementById('board-view-members');
        const viewPendingButton = document.getElementById('board-view-pending');
        const viewCompleteButton = document.getElementById('board-view-complete');
        const viewProgressButton = document.getElementById('board-view-progress');
        const viewStatusMessage = document.getElementById('board-view-status-message');
        const viewEditButton = document.getElementById('board-view-edit');
        const viewArchiveForm = document.getElementById('board-view-archive-form');
        const viewDeleteForm = document.getElementById('board-view-delete-form');
        const viewRequirements = document.getElementById('board-view-requirements');
        const viewRequirementsSummary = document.getElementById('board-view-requirements-summary');
        const viewRequirementsProgress = document.getElementById('board-view-requirements-progress');
        const viewCommentsCount = document.getElementById('board-view-comments-count');
        const viewComments = document.getElementById('board-view-comments');
        const viewWorkAttachments = document.getElementById('board-view-work-attachments');
        const viewCompletionAttachments = document.getElementById('board-view-completion-attachments');
        const viewCommentForm = document.getElementById('board-view-comment-form');
        const commentsChatOpen = document.getElementById('board-comments-chat-open');
        const commentsChatLink = document.getElementById('board-comments-chat-link');
        const commentsChatTotal = document.getElementById('board-comments-chat-total');
        const commentsChatModal = document.getElementById('board-comments-chat-modal');
        const commentsChatBody = document.getElementById('board-comments-chat-body');
        const commentsChatTitle = document.getElementById('board-comments-chat-title');
        const commentsChatForm = document.getElementById('board-comments-chat-form');
        const viewAttachmentForms = Array.from(document.querySelectorAll('.board-view-attachment-form'));
        const editModal = document.getElementById('board-edit-modal');
        const editForm = document.getElementById('board-edit-form');
        const previewModal = document.getElementById('board-attachment-modal');
        const previewTitle = document.getElementById('board-preview-title');
        const previewBody = document.getElementById('board-preview-body');
        const previewDownload = document.getElementById('board-preview-download');
        const descriptionModal = document.getElementById('board-description-modal');
        const descriptionTitle = document.getElementById('board-description-title');
        const descriptionBody = document.getElementById('board-description-body');
        const confirmModal = document.getElementById('board-confirm-modal');
        const confirmTitle = document.getElementById('board-confirm-title');
        const confirmMessage = document.getElementById('board-confirm-message');
        const confirmAccept = confirmModal ? confirmModal.querySelector('[data-confirm-accept]') : null;
        const confirmCancel = confirmModal ? confirmModal.querySelector('[data-confirm-cancel]') : null;
        const replyContext = document.getElementById('board-reply-context');
        const replyContextText = document.getElementById('board-reply-context-text');
        const replyCancel = document.getElementById('board-reply-cancel');
        const chatReplyContext = document.getElementById('board-chat-reply-context');
        const chatReplyContextText = document.getElementById('board-chat-reply-context-text');
        const chatReplyCancel = document.getElementById('board-chat-reply-cancel');
        const boardCurrentUserId = <?= (int)($currentUserId ?? 0) ?>;
        const boardCurrentUserName = <?= json_encode((string)($currentUserName ?? 'Usuario')) ?>;
        let currentBoardVersion = <?= json_encode($boardVersion ?? '') ?>;
        let versionRequestRunning = false;
        let pendingBoardVersion = '';
        let activeViewCard = null;
        let activeViewDescription = '';
        let activeViewComments = [];
        let pendingConfirmForm = null;
        let pendingConfirmCallback = null;
        let draggedCard = null;
        let sourceContainer = null;
        let sourceNextSibling = null;
        let suppressCardOpen = false;

        function showStatus(message, type) {
            if (!status) return;
            status.className = 'board-status ' + type;
            status.textContent = message;
            window.clearTimeout(showStatus.timer);
            showStatus.timer = window.setTimeout(() => {
                status.className = 'board-status';
                status.textContent = '';
            }, type === 'error' ? 5200 : 2200);
        }

        function showViewMessage(message, type = 'ok') {
            if (!viewStatusMessage) return;

            viewStatusMessage.hidden = false;
            viewStatusMessage.className = `board-view-message ${type}`;
            viewStatusMessage.textContent = message;

            window.clearTimeout(showViewMessage.timer);
            showViewMessage.timer = window.setTimeout(() => {
                viewStatusMessage.hidden = true;
                viewStatusMessage.textContent = '';
                viewStatusMessage.className = 'board-view-message';
            }, type === 'error' ? 7600 : 2600);
        }

        function clearViewMessage() {
            if (!viewStatusMessage) return;

            window.clearTimeout(showViewMessage.timer);
            viewStatusMessage.hidden = true;
            viewStatusMessage.textContent = '';
            viewStatusMessage.className = 'board-view-message';
        }

        function updateCommentsChatSendState() {
            if (!commentsChatForm) return;

            const textarea = commentsChatForm.querySelector('textarea[name="body"]');
            const hasText = !!textarea && textarea.value.trim() !== '';
            commentsChatForm.classList.toggle('has-text', hasText);
        }

        function syncBoardVersion(data) {
            if (!data || !data.version) return;

            currentBoardVersion = data.version;
            pendingBoardVersion = '';
            if (liveNotice) {
                liveNotice.hidden = true;
            }
        }

        function updateCounts() {
            document.querySelectorAll('.board-list').forEach(list => {
                const cards = list.querySelectorAll('.board-card');
                const count = list.querySelector('.board-list-count');
                const empty = list.querySelector('.board-empty');
                if (count) count.textContent = cards.length;
                if (empty) empty.style.display = cards.length === 0 ? 'block' : 'none';
            });
        }

        function getDragAfterElement(container, y) {
            const cards = [...container.querySelectorAll('.board-card:not(.dragging)')];
            return cards.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset, element: child };
                }
                return closest;
            }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
        }

        function calculatePosition(card) {
            const previous = card.previousElementSibling?.classList.contains('board-card') ? card.previousElementSibling : null;
            const next = card.nextElementSibling?.classList.contains('board-card') ? card.nextElementSibling : null;
            const previousPos = previous ? Number(previous.dataset.cardPos || 0) : null;
            const nextPos = next ? Number(next.dataset.cardPos || 0) : null;

            if (previousPos !== null && nextPos !== null && nextPos > previousPos) {
                return String((previousPos + nextPos) / 2);
            }

            if (previousPos === null && nextPos !== null) {
                return String(Math.max(1, nextPos / 2));
            }

            if (previousPos !== null && nextPos === null) {
                return String(previousPos + 65536);
            }

            return 'bottom';
        }

        function setCardState(card, completed, inProgress) {
            const check = card.querySelector('.board-card-check');
            const progress = card.querySelector('.board-card-progress');

            card.dataset.cardComplete = completed ? '1' : '0';
            card.dataset.cardProgress = inProgress ? '1' : '0';
            card.classList.toggle('is-complete', completed);
            card.classList.toggle('is-in-progress', inProgress && !completed);

            if (check) {
                check.classList.toggle('is-complete', completed);
                check.setAttribute('aria-pressed', completed ? 'true' : 'false');
                check.title = completed ? 'Desmarcar tarjeta' : 'Marcar tarjeta';
            }

            if (progress) {
                const active = inProgress && !completed;
                progress.classList.toggle('is-active', active);
                progress.setAttribute('aria-pressed', active ? 'true' : 'false');
                progress.title = active ? 'Volver a pendiente' : 'Marcar en proceso';
                progress.setAttribute('aria-label', progress.title);
                progress.innerHTML = active ? '&#128309;' : '&#128640;';
            }

            if (activeViewCard === card) {
                syncViewState(card);
            }
        }

        async function postForm(url, params) {
            const body = new URLSearchParams(params);
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body
            });

            const data = await response.json();
            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'No se pudo guardar el cambio.');
            }
            return data;
        }

        function formatCardDate(value) {
            if (!value) return 'Sin fecha';

            const parts = String(value).split('-');
            if (parts.length === 3) {
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }

            return value;
        }

        function cardDisplayTitle(card) {
            const title = (card.dataset.cardTitle || '').trim();
            return title || 'Sin titulo';
        }

        const DESCRIPTION_PREVIEW_LIMIT = 420;

        function setViewDescription(text) {
            activeViewDescription = (text || '').trim() || 'Sin descripcion cargada.';

            if (!viewDescription) return;

            const shouldTruncate = activeViewDescription.length > DESCRIPTION_PREVIEW_LIMIT;
            viewDescription.textContent = shouldTruncate
                ? activeViewDescription.substring(0, DESCRIPTION_PREVIEW_LIMIT).trimEnd() + '...'
                : activeViewDescription;

            if (viewDescriptionMore) {
                viewDescriptionMore.hidden = !shouldTruncate;
            }
        }

        function openDescriptionModal() {
            if (!descriptionModal || !descriptionBody) return;

            if (descriptionTitle) {
                descriptionTitle.textContent = viewTitle?.textContent || 'Descripcion completa';
            }

            descriptionBody.textContent = activeViewDescription || 'Sin descripcion cargada.';
            descriptionModal.hidden = false;
        }

        function closeDescriptionModal() {
            if (descriptionModal) {
                descriptionModal.hidden = true;
            }
        }

        function openCommentsChatModal() {
            if (!commentsChatModal || !activeViewCard) return;

            if (commentsChatTitle) {
                commentsChatTitle.textContent = cardDisplayTitle(activeViewCard);
            }

            if (commentsChatForm) {
                commentsChatForm.action = commentUrlTemplate.replace('__ID__', encodeURIComponent(activeViewCard.dataset.cardId));
                commentsChatForm.reset();
                clearChatReplyTarget();
                updateCommentsChatSendState();
            }

            renderChatComments(activeViewComments);
            commentsChatModal.hidden = false;

            window.requestAnimationFrame(() => {
                if (commentsChatBody) {
                    commentsChatBody.scrollTop = commentsChatBody.scrollHeight;
                }
                commentsChatForm?.querySelector('textarea[name="body"]')?.focus();
            });
        }

        function closeCommentsChatModal() {
            if (!commentsChatModal) return;
            commentsChatModal.hidden = true;
            clearChatReplyTarget();
        }

        function memberInitials(name) {
            const cleanName = String(name || '').trim();
            if (!cleanName) return '?';
            return cleanName.substring(0, 2).toUpperCase();
        }

        function renderViewMembers(card) {
            if (!viewMembers) return;

            const names = (card.dataset.cardAssigneeNames || '')
                .split('|')
                .map(name => name.trim())
                .filter(Boolean);
            const titles = (card.dataset.cardAssigneeTitles || '')
                .split('|')
                .map(title => title.trim());

            viewMembers.replaceChildren();

            if (names.length === 0) {
                const empty = document.createElement('span');
                empty.className = 'board-view-empty';
                empty.textContent = 'Sin asignar.';
                viewMembers.appendChild(empty);
                return;
            }

            names.forEach((name, index) => {
                const row = document.createElement('div');
                row.className = 'board-view-member';

                const avatar = document.createElement('span');
                avatar.className = 'board-view-member-avatar';
                avatar.textContent = memberInitials(name);

                const label = document.createElement('span');
                label.className = 'board-view-member-copy';

                const nameNode = document.createElement('span');
                nameNode.className = 'board-view-member-name';
                nameNode.textContent = name;

                const titleNode = document.createElement('span');
                titleNode.className = 'board-view-member-title';
                titleNode.textContent = titles[index] || 'Equipo OfficeHub';

                label.append(nameNode, titleNode);
                row.appendChild(avatar);
                row.appendChild(label);
                viewMembers.appendChild(row);
            });
        }

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value == null ? '' : String(value);
            return div.innerHTML;
        }

        function formatFileSize(size) {
            const bytes = Number(size || 0);
            if (bytes >= 1048576) return `${(bytes / 1048576).toFixed(1)} MB`;
            if (bytes >= 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;
            return `${bytes} B`;
        }

        function formatTraceDate(value) {
            if (!value) return '';
            const date = new Date(String(value).replace(' ', 'T'));
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleString('es-AR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
        }

        function commentPreview(value, limit = 90) {
            const text = String(value || '').replace(/\s+/g, ' ').trim();
            if (text.length <= limit) return text;
            return text.substring(0, limit).trimEnd() + '...';
        }

        function clearReplyTargetFor(form, context, textNode) {
            if (!form) return;

            const input = form.querySelector('input[name="reply_to_comment_id"]');
            if (input) {
                input.value = '';
            }

            if (context) {
                context.hidden = true;
            }

            if (textNode) {
                textNode.textContent = '';
            }
        }

        function clearReplyTarget() {
            clearReplyTargetFor(viewCommentForm, replyContext, replyContextText);
        }

        function clearChatReplyTarget() {
            clearReplyTargetFor(commentsChatForm, chatReplyContext, chatReplyContextText);
        }

        function clearAllReplyTargets() {
            clearReplyTarget();
            clearChatReplyTarget();
        }

        function setReplyTargetFor(form, context, textNode, commentId, authorName, body) {
            if (!form || !commentId) return;

            const input = form.querySelector('input[name="reply_to_comment_id"]');
            const textarea = form.querySelector('textarea[name="body"]');

            if (input) {
                input.value = String(commentId);
            }

            if (context && textNode) {
                textNode.innerHTML = `<strong>Respondiendo a ${escapeHtml(authorName || 'Usuario')}</strong>${escapeHtml(commentPreview(body))}`;
                context.hidden = false;
            }

            textarea?.focus();
        }

        function setReplyTarget(commentId, authorName, body) {
            setReplyTargetFor(viewCommentForm, replyContext, replyContextText, commentId, authorName, body);
        }

        function setChatReplyTarget(commentId, authorName, body) {
            setReplyTargetFor(commentsChatForm, chatReplyContext, chatReplyContextText, commentId, authorName, body);
        }

        function syncRequirementBadges(card) {
            if (!card) return;

            const hasDocumentation = card.dataset.cardHasDocumentation === '1';
            const hasFinalComment = card.dataset.cardHasFinalComment === '1';
            const docBadge = card.querySelector('.board-card-requirement-doc');
            const finalBadge = card.querySelector('.board-card-requirement-final');

            if (docBadge) {
                docBadge.classList.toggle('is-ok', hasDocumentation);
                docBadge.classList.toggle('is-missing', !hasDocumentation);
                docBadge.title = hasDocumentation ? 'Documentacion entregada' : 'Requiere documentacion para completarse';
                docBadge.innerHTML = `&#128196;${hasDocumentation ? '&#9989;' : ''}`;
            }

            if (finalBadge) {
                finalBadge.classList.toggle('is-ok', hasFinalComment);
                finalBadge.classList.toggle('is-missing', !hasFinalComment);
                finalBadge.title = hasFinalComment ? 'Comentario final cargado' : 'Requiere comentario final para completarse';
                finalBadge.innerHTML = `&#128172;${hasFinalComment ? '&#9989;' : ''}`;
            }
        }

        function syncActiveCardCommentCount() {
            if (!activeViewCard) return;

            const count = activeViewComments.length;
            activeViewCard.dataset.cardCommentCount = String(count);

            const row = activeViewCard.querySelector('.board-badge-row');
            if (!row) return;

            let badge = row.querySelector('.board-card-comment-count');
            if (count <= 0) {
                if (badge) badge.remove();
                return;
            }

            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'board-badge board-card-icon board-card-comment-count has-count';
                const deleteForm = row.querySelector('.board-card-action-form');
                if (deleteForm) {
                    row.insertBefore(badge, deleteForm);
                } else {
                    row.appendChild(badge);
                }
            }

            const label = count === 1 ? '1 comentario' : `${count} comentarios`;
            badge.title = label;
            badge.innerHTML = `&#128172;<span>${count}</span>`;
        }

        function renderRequirements(card, completion) {
            if (!viewRequirements) return;

            const requiresDocumentation = card.dataset.cardRequiresDocumentation === '1';
            const requiresFinalComment = card.dataset.cardRequiresFinalComment === '1';
            const hasDocumentation = !!completion?.has_documentation;
            const hasFinalComment = !!completion?.has_final_comment;

            if (completion && typeof completion === 'object') {
                card.dataset.cardHasDocumentation = hasDocumentation ? '1' : '0';
                card.dataset.cardHasFinalComment = hasFinalComment ? '1' : '0';
                syncRequirementBadges(card);
            }

            viewRequirements.replaceChildren();

            const rows = [];

            if (requiresDocumentation) {
                rows.push({
                    ok: hasDocumentation,
                    text: 'Documentacion obligatoria'
                });
            }

            if (requiresFinalComment) {
                rows.push({
                    ok: hasFinalComment,
                    text: 'Comentario final obligatorio'
                });
            }

            const completedRows = rows.filter(row => row.ok).length;
            const totalRows = rows.length;
            const progress = totalRows > 0 ? Math.round((completedRows / totalRows) * 100) : 0;

            if (viewRequirementsSummary) {
                viewRequirementsSummary.textContent = totalRows > 0 ? `${completedRows}/${totalRows} completados` : 'Sin requisitos';
            }

            if (viewRequirementsProgress) {
                viewRequirementsProgress.hidden = totalRows === 0;
                const bar = viewRequirementsProgress.querySelector('span');
                if (bar) {
                    bar.style.width = `${progress}%`;
                }
            }

            if (!requiresDocumentation && !requiresFinalComment) {
                const empty = document.createElement('span');
                empty.className = 'board-view-empty';
                empty.textContent = 'Esta tarjeta no tiene requisitos especiales.';
                viewRequirements.appendChild(empty);
                return;
            }

            rows.forEach(row => {
                const item = document.createElement('span');
                item.className = `board-requirement-status ${row.ok ? 'is-ok' : 'is-missing'}`;
                item.innerHTML = `
                    <span class="board-requirement-main">
                        <span class="board-requirement-dot ${row.ok ? 'is-ok' : ''}">${row.ok ? 'OK' : ''}</span>
                        <span>${escapeHtml(row.text)}</span>
                    </span>
                    <strong>${row.ok ? 'OK' : 'Pendiente'}</strong>
                `;
                viewRequirements.appendChild(item);
            });
        }

        function renderComments(comments) {
            if (!viewComments) return;

            activeViewComments = Array.isArray(comments) ? comments : [];
            viewComments.replaceChildren();

            if (viewCommentsCount) {
                viewCommentsCount.textContent = activeViewComments.length > 0 ? `(${activeViewComments.length})` : '(0)';
            }

            if (commentsChatTotal) {
                commentsChatTotal.textContent = String(activeViewComments.length);
            }

            syncActiveCardCommentCount();

            const hasComments = activeViewComments.length > 0;
            const shouldShowChatLink = activeViewComments.length > 2;

            if (commentsChatOpen) {
                commentsChatOpen.hidden = !hasComments;
            }

            if (commentsChatLink) {
                commentsChatLink.hidden = !shouldShowChatLink;
            }

            if (!hasComments) {
                const empty = document.createElement('span');
                empty.className = 'board-view-empty';
                empty.textContent = 'Todavia no hay comentarios.';
                viewComments.appendChild(empty);
                renderChatComments([]);
                return;
            }

            activeViewComments.slice(0, 4).forEach(comment => {
                const isFinal = Number(comment.is_final || 0) === 1;
                const commentUserId = Number(comment.user_id || 0);
                const isOwn = commentUserId > 0 && commentUserId === boardCurrentUserId;
                const canReply = commentUserId > 0 && !isOwn;
                const item = document.createElement('article');
                item.className = `board-trace-item ${isFinal ? 'is-final' : ''}`;
                const replyAuthor = comment.reply_author_name || '';
                const replyBody = comment.reply_body || '';
                const replyBlock = replyAuthor || replyBody
                    ? `<div class="board-trace-reply">
                            <strong>Respuesta a ${escapeHtml(replyAuthor || 'Usuario')}</strong>
                            ${escapeHtml(commentPreview(replyBody || 'Comentario'))}
                       </div>`
                    : '';
                item.innerHTML = `
                    <div class="board-trace-meta">
                        <strong>${escapeHtml(comment.author_name || 'Usuario')}</strong>
                        <span>${isFinal ? '<span class="board-trace-final">Cierre</span> - ' : ''}${escapeHtml(formatTraceDate(comment.created_at))}</span>
                    </div>
                    <div class="board-trace-body">${escapeHtml(commentPreview(comment.body || '', 160))}</div>
                    ${replyBlock}
                    ${canReply ? `
                        <div class="board-trace-actions">
                            <button type="button"
                                class="board-comment-reply"
                                data-reply-comment-id="${Number(comment.id || 0)}"
                                data-reply-author="${escapeHtml(comment.author_name || 'Usuario')}"
                                data-reply-body="${escapeHtml(comment.body || '')}">
                                Responder
                            </button>
                        </div>
                    ` : ''}
                `;
                viewComments.appendChild(item);
            });

            renderChatComments(activeViewComments);
        }

        function renderChatComments(comments) {
            if (!commentsChatBody) return;

            commentsChatBody.replaceChildren();

            if (!comments || comments.length === 0) {
                const empty = document.createElement('span');
                empty.className = 'board-view-empty';
                empty.textContent = 'Todavia no hay comentarios.';
                commentsChatBody.appendChild(empty);
                return;
            }

            comments.forEach(comment => {
                const commentUserId = Number(comment.user_id || 0);
                const isOwn = commentUserId > 0 && commentUserId === boardCurrentUserId;
                const isFinal = Number(comment.is_final || 0) === 1;
                const canReply = commentUserId > 0 && !isOwn;
                const replyAuthor = comment.reply_author_name || '';
                const replyBody = comment.reply_body || '';
                const replyBlock = replyAuthor || replyBody
                    ? `<span class="board-chat-reply"><strong>Respuesta a ${escapeHtml(replyAuthor || 'Usuario')}</strong>${escapeHtml(commentPreview(replyBody || 'Comentario'))}</span>`
                    : '';
                const metaText = `${escapeHtml(comment.author_name || 'Usuario')} - ${escapeHtml(formatTraceDate(comment.created_at))}${isFinal ? ' - Cierre' : ''}`;
                const replyButton = canReply
                    ? `<button type="button"
                            class="board-comment-reply"
                            data-chat-reply="1"
                            data-reply-comment-id="${Number(comment.id || 0)}"
                            data-reply-author="${escapeHtml(comment.author_name || 'Usuario')}"
                            data-reply-body="${escapeHtml(comment.body || '')}">
                            Responder
                       </button>`
                    : '';

                const item = document.createElement('article');
                item.className = `board-chat-message ${isOwn ? 'is-own' : 'is-other'} ${isFinal ? 'is-final' : ''}`;
                item.innerHTML = `<div class="board-chat-meta">${metaText}</div><div class="board-chat-bubble"><span class="board-chat-text">${escapeHtml(comment.body || '')}</span>${replyBlock}</div>${replyButton}`;
                commentsChatBody.appendChild(item);
            });
        }

        function optimisticCommentFromForm(form, body) {
            if (!form || !body || (form !== viewCommentForm && form !== commentsChatForm)) {
                return null;
            }

            const text = String(body.get('body') || '').trim();
            if (text === '') {
                return null;
            }

            const replyToCommentId = Number(body.get('reply_to_comment_id') || 0);
            const replyTo = replyToCommentId > 0
                ? activeViewComments.find(comment => Number(comment.id || 0) === replyToCommentId)
                : null;

            return {
                id: -Date.now(),
                user_id: boardCurrentUserId,
                author_name: boardCurrentUserName || 'Usuario',
                body: text,
                is_final: String(body.get('is_final') || '0') === '1' ? 1 : 0,
                created_at: new Date().toISOString(),
                reply_to_comment_id: replyToCommentId || null,
                reply_author_name: replyTo?.author_name || '',
                reply_body: replyTo?.body || '',
                optimistic: true
            };
        }

        function paintOptimisticComment(comment, form) {
            if (!comment) return;

            activeViewComments = [...activeViewComments, comment];
            renderComments(activeViewComments);

            if (form === commentsChatForm && commentsChatBody) {
                window.requestAnimationFrame(() => {
                    commentsChatBody.scrollTop = commentsChatBody.scrollHeight;
                });
            }
        }

        function removeOptimisticComment(comment) {
            if (!comment) return;

            activeViewComments = activeViewComments.filter(item => item !== comment);
            renderComments(activeViewComments);
        }

        function clearComposerAfterOptimistic(form, body) {
            if (!form || !body || (form !== viewCommentForm && form !== commentsChatForm)) return;

            form.reset();

            if (form === commentsChatForm) {
                clearChatReplyTarget();
                updateCommentsChatSendState();
            } else {
                clearReplyTarget();
            }
        }

        function restoreComposerAfterOptimisticError(form, body) {
            if (!form || !body || (form !== viewCommentForm && form !== commentsChatForm)) return;

            const textarea = form.querySelector('textarea[name="body"]');
            const isFinal = form.querySelector('input[name="is_final"]');

            if (textarea) {
                textarea.value = String(body.get('body') || '');
            }

            if (isFinal) {
                isFinal.checked = String(body.get('is_final') || '0') === '1';
            }

            if (form === commentsChatForm) {
                updateCommentsChatSendState();
            }
        }

        function applyServerComment(comment, optimisticComment) {
            if (!comment) return false;

            if (optimisticComment) {
                comment.reply_to_comment_id = comment.reply_to_comment_id || optimisticComment.reply_to_comment_id || null;
                comment.reply_author_name = comment.reply_author_name || optimisticComment.reply_author_name || '';
                comment.reply_body = comment.reply_body || optimisticComment.reply_body || '';
            }

            const serverId = Number(comment.id || 0);
            activeViewComments = activeViewComments.filter(item => {
                if (optimisticComment && item === optimisticComment) return false;
                return serverId <= 0 || Number(item.id || 0) !== serverId;
            });
            activeViewComments.push(comment);
            renderComments(activeViewComments);

            return true;
        }

        function renderAttachmentGroup(container, attachments, emptyText) {
            if (!container) return;
            container.replaceChildren();

            if (!attachments || attachments.length === 0) {
                const empty = document.createElement('span');
                empty.className = 'board-view-empty';
                empty.textContent = emptyText;
                container.appendChild(empty);
                return;
            }

            attachments.forEach(attachment => {
                const card = document.createElement('article');
                card.className = 'board-attachment-card';

                const preview = attachment.is_image
                    ? `<img src="${escapeHtml(attachment.url)}" alt="${escapeHtml(attachment.filename)}">`
                    : '<span class="board-attachment-icon">DOC</span>';

                card.innerHTML = `
                    ${preview}
                    <strong>${escapeHtml(attachment.filename || 'Adjunto')}</strong>
                    <span>${escapeHtml(formatFileSize(attachment.size))} - ${escapeHtml(attachment.uploader_name || 'Usuario')}</span>
                    <div class="board-attachment-actions">
                        <button type="button"
                            class="board-attachment-action"
                            data-preview-attachment
                            data-preview-url="${escapeHtml(attachment.preview_url || attachment.url)}"
                            data-download-url="${escapeHtml(attachment.download_url || (attachment.url + '?download=1'))}"
                            data-preview-name="${escapeHtml(attachment.filename || 'Adjunto')}"
                            data-preview-mime="${escapeHtml(attachment.mime_type || '')}">
                            Ver
                        </button>
                        <a class="board-attachment-action"
                            href="${escapeHtml(attachment.download_url || (attachment.url + '?download=1'))}">
                            Descargar
                        </a>
                        <button type="button"
                            class="board-attachment-action board-attachment-delete"
                            data-delete-attachment
                            data-delete-url="${escapeHtml(attachment.delete_url || '')}"
                            data-delete-name="${escapeHtml(attachment.filename || 'Adjunto')}">
                            Eliminar
                        </button>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function renderAttachments(attachments) {
            const items = Array.isArray(attachments) ? attachments : [];
            const workAttachments = items.filter(attachment => attachment.purpose === 'work');
            const completionAttachments = items.filter(attachment => attachment.purpose !== 'work');

            renderAttachmentGroup(viewWorkAttachments, workAttachments, 'Todavia no hay documentacion de trabajo.');
            renderAttachmentGroup(viewCompletionAttachments, completionAttachments, 'Todavia no hay entregables de cierre.');
        }

        function openAttachmentPreview(url, downloadUrl, name, mimeType) {
            if (!previewModal || !previewBody) return;

            const mime = String(mimeType || '').toLowerCase();
            const safeName = name || 'Adjunto';
            const extension = safeName.split('.').pop().toLowerCase();
            const officeExtensions = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
            const isOffice = officeExtensions.includes(extension);
            const previewUrl = isOffice ? withPreviewCacheBust(url) : url;

            if (previewTitle) {
                previewTitle.textContent = safeName;
            }

            if (previewDownload) {
                previewDownload.href = downloadUrl || url;
            }

            previewBody.replaceChildren();

            if (mime.startsWith('image/')) {
                const image = document.createElement('img');
                image.src = previewUrl;
                image.alt = safeName;
                previewBody.appendChild(image);
            } else if (mime === 'application/pdf' || mime.startsWith('text/') || isOffice) {
                const frame = document.createElement('iframe');
                frame.src = previewUrl;
                frame.title = safeName;
                previewBody.appendChild(frame);
            } else {
                const fallback = document.createElement('div');
                fallback.className = 'board-preview-fallback';
                fallback.innerHTML = `
                    <strong style="color:var(--text-1);">Vista previa no disponible para este formato.</strong><br>
                    Los documentos de Office dependen del equipo/navegador. Podes descargarlo desde el boton inferior.
                `;
                previewBody.appendChild(fallback);
            }

            previewModal.hidden = false;
        }

        async function deleteAttachment(button) {
            if (!button || button.disabled) return;

            const url = button.dataset.deleteUrl || '';

            if (!url) {
                showViewMessage('No se encontro la ruta para eliminar el adjunto.', 'error');
                return;
            }

            button.disabled = true;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams()
                });

                const data = await response.json();

                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'No se pudo eliminar el adjunto.');
                }

                if (Array.isArray(data.attachments)) {
                    renderAttachments(data.attachments);
                }

                if (data.completion) {
                    renderRequirements(activeViewCard, data.completion);
                }

                syncBoardVersion(data);
                showStatus(data.message || 'Adjunto eliminado.', 'ok');
                showViewMessage(data.message || 'Adjunto eliminado.', 'ok');
            } catch (error) {
                button.disabled = false;
                showStatus(error.message, 'error');
                showViewMessage(error.message, 'error');
            }
        }

        function withPreviewCacheBust(url) {
            try {
                const nextUrl = new URL(url, window.location.href);
                nextUrl.searchParams.set('_preview_try', Date.now().toString());
                return nextUrl.toString();
            } catch (error) {
                return url + (url.includes('?') ? '&' : '?') + '_preview_try=' + Date.now();
            }
        }

        function closeAttachmentPreview() {
            if (!previewModal || !previewBody) return;
            previewModal.hidden = true;
            previewBody.replaceChildren();
        }

        async function loadCardTrace(card) {
            if (!card) return;

            if (viewRequirements) {
                viewRequirements.innerHTML = '<span class="board-view-empty">Cargando requisitos...</span>';
            }
            if (viewComments) {
                viewComments.innerHTML = '<span class="board-view-empty">Cargando comentarios...</span>';
            }
            if (viewWorkAttachments) {
                viewWorkAttachments.innerHTML = '<span class="board-view-empty">Cargando documentacion de trabajo...</span>';
            }
            if (viewCompletionAttachments) {
                viewCompletionAttachments.innerHTML = '<span class="board-view-empty">Cargando entregables de cierre...</span>';
            }

            const url = traceUrlTemplate.replace('__ID__', encodeURIComponent(card.dataset.cardId));

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    cache: 'no-store',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();

                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'No se pudo cargar la trazabilidad.');
                }

                renderRequirements(card, data.completion || {});
                renderComments(data.comments || []);
                renderAttachments(data.attachments || []);
                syncBoardVersion(data);
            } catch (error) {
                if (viewComments) {
                    viewComments.innerHTML = `<span class="board-view-empty">${escapeHtml(error.message)}</span>`;
                }
                renderRequirements(card, null);
                renderAttachments([]);
            }
        }

        async function submitTraceForm(form) {
            if (!form || !activeViewCard || form.dataset.submitting === '1') return;

            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton ? submitButton.textContent : '';
            const isIconSubmit = !!submitButton && submitButton.classList.contains('board-comments-chat-send');
            const body = new FormData(form);
            const optimisticComment = optimisticCommentFromForm(form, body);

            form.dataset.submitting = '1';
            form.setAttribute('aria-busy', 'true');
            paintOptimisticComment(optimisticComment, form);
            clearComposerAfterOptimistic(form, optimisticComment ? body : null);

            if (submitButton) {
                submitButton.disabled = true;
                if (!isIconSubmit) {
                    submitButton.textContent = 'Guardando...';
                }
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body
                });

                const data = await response.json();

                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'No se pudo guardar el cambio.');
                }

                form.reset();
                if (form === commentsChatForm) {
                    clearChatReplyTarget();
                    updateCommentsChatSendState();
                } else {
                    clearReplyTarget();
                }
                if (data.completion) {
                    renderRequirements(activeViewCard, data.completion);
                }

                if (Array.isArray(data.comments)) {
                    renderComments(data.comments);
                } else if (data.comment) {
                    applyServerComment(data.comment, optimisticComment);
                }

                if (Array.isArray(data.attachments)) {
                    renderAttachments(data.attachments);
                }

                syncBoardVersion(data);
                if (form === commentsChatForm && commentsChatBody) {
                    window.requestAnimationFrame(() => {
                        commentsChatBody.scrollTop = commentsChatBody.scrollHeight;
                    });
                }
                showStatus(data.message || 'Cambio guardado.', 'ok');
                showViewMessage(data.message || 'Cambio guardado.', 'ok');
            } catch (error) {
                removeOptimisticComment(optimisticComment);
                restoreComposerAfterOptimisticError(form, optimisticComment ? body : null);
                showStatus(error.message, 'error');
                showViewMessage(error.message, 'error');
            } finally {
                form.dataset.submitting = '0';
                form.removeAttribute('aria-busy');

                if (submitButton) {
                    submitButton.disabled = false;
                    if (!isIconSubmit) {
                        submitButton.textContent = originalText;
                    }
                }
                if (form === commentsChatForm) {
                    updateCommentsChatSendState();
                }
            }
        }

        function syncViewState(card) {
            if (!card || activeViewCard !== card) return;

            const completed = card.dataset.cardComplete === '1';
            const inProgress = card.dataset.cardProgress === '1' && !completed;

            if (viewStatusText) {
                viewStatusText.textContent = completed ? 'Completada' : (inProgress ? 'En proceso' : 'Pendiente');
            }

            if (viewPendingButton) {
                const selected = !completed && !inProgress;
                viewPendingButton.classList.toggle('is-selected', selected);
                viewPendingButton.disabled = selected;
            }

            if (viewProgressButton) {
                viewProgressButton.textContent = 'En proceso';
                viewProgressButton.classList.toggle('is-selected', inProgress);
                viewProgressButton.disabled = inProgress;
            }

            if (viewCompleteButton) {
                viewCompleteButton.textContent = 'Marcar completa';
                viewCompleteButton.classList.toggle('is-selected', completed);
                viewCompleteButton.disabled = completed;
            }

            if (viewArchiveForm) {
                viewArchiveForm.hidden = !completed;
            }
            
        }

        function openViewModal(card) {
            if (!viewModal || !card) return;

            activeViewCard = card;

            const listTitle = card.closest('.board-list')?.querySelector('.board-list-title')?.textContent?.trim() || 'Tablero';
            const labelText = (card.dataset.cardLabelText || '').trim();
            const labelColor = (card.dataset.cardLabelColor || '').trim();

            if (viewList) viewList.textContent = `${listTitle} - ID: ${card.dataset.cardId || 'CARD'}`;
            if (viewTitle) viewTitle.textContent = cardDisplayTitle(card);
            setViewDescription(card.dataset.cardDescription || '');
            if (viewDue) viewDue.textContent = formatCardDate(card.dataset.cardDueDate || '');
            if (viewArchive) viewArchive.textContent = card.dataset.cardAutoArchive === '1' ? 'Automatico' : 'Permanente';
            clearViewMessage();

            if (viewLabel) {
                const hasLabel = !!(labelText || labelColor);
                viewLabel.hidden = !hasLabel;
                if (hasLabel) {
                    if (viewLabelSwatch) viewLabelSwatch.style.background = labelColor || '#58a6ff';
                    if (viewLabelText) viewLabelText.textContent = labelText || 'Etiqueta';
                }
            }

            if (viewArchiveForm) {
                viewArchiveForm.action = archiveUrlTemplate.replace(
                    '__ID__',
                    encodeURIComponent(card.dataset.cardId)
                );
            }

            if (viewDeleteForm) {
                viewDeleteForm.action = deleteUrlTemplate.replace(
                    '__ID__',
                    encodeURIComponent(card.dataset.cardId)
                );
            }

            if (viewCommentForm) {
                viewCommentForm.action = commentUrlTemplate.replace('__ID__', encodeURIComponent(card.dataset.cardId));
                viewCommentForm.reset();
                clearReplyTarget();
            }

            if (commentsChatForm) {
                commentsChatForm.action = commentUrlTemplate.replace('__ID__', encodeURIComponent(card.dataset.cardId));
                commentsChatForm.reset();
                clearChatReplyTarget();
                updateCommentsChatSendState();
            }

            viewAttachmentForms.forEach(form => {
                form.action = attachmentUrlTemplate.replace('__ID__', encodeURIComponent(card.dataset.cardId));
                form.reset();
            });

            renderViewMembers(card);
            syncViewState(card);
            renderRequirements(card, null);
            loadCardTrace(card);
            viewModal.hidden = false;

            window.requestAnimationFrame(() => {
                viewModal.querySelector('[data-view-close]')?.focus();
            });
        }

        function closeViewModal() {
            if (!viewModal) return;
            viewModal.hidden = true;
            closeCommentsChatModal();
            clearAllReplyTargets();
            activeViewCard = null;
        }

        function openEditModal(card) {
            if (!editModal || !editForm) return;

            const assigneeIds = (card.dataset.cardAssignees || '')
                .split(',')
                .map(id => id.trim())
                .filter(Boolean);

            editForm.action = updateUrlTemplate.replace('__ID__', encodeURIComponent(card.dataset.cardId));
            editForm.elements.title.value = card.dataset.cardTitle || '';
            editForm.elements.description.value = card.dataset.cardDescription || '';
            editForm.elements.label_text.value = card.dataset.cardLabelText || '';
            editForm.elements.label_color.value = card.dataset.cardLabelColor || '#58a6ff';
            editForm.elements.due_date.value = card.dataset.cardDueDate || '';
            editForm.elements.auto_archive_on_complete.checked = card.dataset.cardAutoArchive === '1';
            editForm.elements.requires_documentation.checked = card.dataset.cardRequiresDocumentation === '1';
            editForm.elements.requires_final_comment.checked = card.dataset.cardRequiresFinalComment === '1';

            const withoutAssigneeInput = editForm.querySelector('[data-without-assignee]');
            const unassignedToggle = editForm.querySelector('[data-unassigned-toggle]');
            const assigneeInputs = [...editForm.querySelectorAll('input[name="assignees[]"]')];

            assigneeInputs.forEach(input => {
                input.checked = assigneeIds.includes(input.value);
            });

            if (unassignedToggle && withoutAssigneeInput) {
                const isUnassigned = assigneeIds.length === 0;
                unassignedToggle.checked = isUnassigned;
                withoutAssigneeInput.value = isUnassigned ? '1' : '0';
            }

            editModal.hidden = false;
            window.requestAnimationFrame(() => {
                editForm.dataset.initialSnapshot = formSnapshot(editForm);
                editForm.elements.title.focus();
            });
        }

        function closeEditModal(force = false) {
            if (!editModal || !editForm) return;

            if (!force && editForm.dataset.initialSnapshot !== formSnapshot(editForm)) {
                return;
            }

            editModal.hidden = true;
        }

        function formSnapshot(form) {
            return [...form.elements].map(field => {
                if (!field.name) return null;

                if (field.type === 'checkbox' || field.type === 'radio') {
                    return `${field.name}:${field.value}:${field.checked ? '1' : '0'}`;
                }

                return `${field.name}:${field.value}`;
            }).filter(Boolean).join('|');
        }

        function saveDetailsSnapshot(details) {
            const form = details.querySelector('.board-form');
            if (!form) return;
            form.dataset.initialSnapshot = formSnapshot(form);
        }

        function detailsHasUnsavedChanges(details) {
            const form = details.querySelector('.board-form');
            if (!form) return false;
            return form.dataset.initialSnapshot !== formSnapshot(form);
        }

        function isBoardBusy() {
            if (draggedCard) return true;
            if (document.querySelector('.board-form[aria-busy="true"]')) return true;
            if (viewModal && !viewModal.hidden) return true;
            if (editModal && !editModal.hidden) return true;
            if (previewModal && !previewModal.hidden) return true;
            if (descriptionModal && !descriptionModal.hidden) return true;
            if (commentsChatModal && !commentsChatModal.hidden) return true;
            if (confirmModal && !confirmModal.hidden) return true;

            const activeElement = document.activeElement;
            if (
                activeElement
                && activeElement.closest('.board-page')
                && activeElement.matches('input, textarea, select, button')
            ) {
                return true;
            }

            return !!document.querySelector('details.board-add-card[open], details.board-add-list[open]');
        }

        function showLiveNotice(version) {
            pendingBoardVersion = version || pendingBoardVersion;
            if (liveNotice) {
                liveNotice.hidden = false;
            }
        }

        async function checkBoardVersion() {
            if (!versionUrl || versionRequestRunning) return;
            if (document.hidden) return;

            versionRequestRunning = true;

            try {
                const response = await fetch(versionUrl, {
                    method: 'GET',
                    cache: 'no-store',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();

                if (!response.ok || !data.ok || !data.version) {
                    return;
                }

                if (!currentBoardVersion) {
                    currentBoardVersion = data.version;
                    return;
                }

                if (data.version !== currentBoardVersion) {
                    if (isBoardBusy()) {
                        showLiveNotice(data.version);
                    } else {
                        window.location.reload();
                    }
                }
            } catch (error) {
                // El polling no debe interrumpir el uso normal del tablero.
            } finally {
                versionRequestRunning = false;
            }
        }

        document.querySelectorAll('details.board-add-card, details.board-add-list').forEach(details => {
            details.addEventListener('toggle', () => {
                if (details.open) {
                    window.requestAnimationFrame(() => saveDetailsSnapshot(details));
                }
            });
        });

        document.addEventListener('pointerdown', event => {
            document.querySelectorAll('details.board-add-card[open], details.board-add-list[open]').forEach(details => {
                if (details.contains(event.target)) {
                    return;
                }

                if (!detailsHasUnsavedChanges(details)) {
                    details.open = false;
                }
            });
        });

        document.querySelectorAll('.board-card-edit').forEach(button => {
            button.addEventListener('pointerdown', event => {
                event.stopPropagation();
            });

            button.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();

                const card = button.closest('.board-card');
                if (card) {
                    openEditModal(card);
                }
            });
        });

        if (viewModal) {
            viewModal.addEventListener('pointerdown', event => {
                if (event.target === viewModal) {
                    closeViewModal();
                }
            });
        }

        document.querySelectorAll('[data-view-close]').forEach(button => {
            button.addEventListener('click', () => closeViewModal());
        });

        if (viewDescriptionMore) {
            viewDescriptionMore.addEventListener('click', openDescriptionModal);
        }

        if (descriptionModal) {
            descriptionModal.addEventListener('pointerdown', event => {
                if (event.target === descriptionModal) {
                    closeDescriptionModal();
                }
            });
        }

        document.querySelectorAll('[data-description-close]').forEach(button => {
            button.addEventListener('click', () => closeDescriptionModal());
        });

        [commentsChatOpen, commentsChatLink].forEach(trigger => {
            if (!trigger) return;
            trigger.addEventListener('click', event => {
                const button = event.target.closest('button');
                if (!button && trigger !== commentsChatOpen) return;
                openCommentsChatModal();
            });
        });

        if (commentsChatModal) {
            commentsChatModal.addEventListener('pointerdown', event => {
                if (event.target === commentsChatModal) {
                    closeCommentsChatModal();
                }
            });
        }

        document.querySelectorAll('[data-comments-chat-close]').forEach(button => {
            button.addEventListener('click', () => closeCommentsChatModal());
        });

        if (viewEditButton) {
            viewEditButton.addEventListener('click', () => {
                const card = activeViewCard;
                closeViewModal();
                if (card) {
                    openEditModal(card);
                }
            });
        }

        async function updateActiveViewStatus(targetStatus) {
            const card = activeViewCard;
            if (!card) return;

            const previousCompleted = card.dataset.cardComplete === '1';
            const previousInProgress = card.dataset.cardProgress === '1';
            const isAlreadyPending = !previousCompleted && !previousInProgress;
            const isAlreadyProgress = previousInProgress && !previousCompleted;

            if (
                (targetStatus === 'pending' && isAlreadyPending) ||
                (targetStatus === 'progress' && isAlreadyProgress) ||
                (targetStatus === 'complete' && previousCompleted)
            ) {
                return;
            }

            const statusButtons = [viewPendingButton, viewProgressButton, viewCompleteButton].filter(Boolean);
            let url = completeUrl;
            let params = { card_id: card.dataset.cardId, completed: '0' };
            let successMessage = 'Tarjeta pendiente.';
            let optimisticCompleted = false;
            let optimisticInProgress = false;

            if (targetStatus === 'progress') {
                url = progressUrl;
                params = { card_id: card.dataset.cardId, in_progress: '1' };
                successMessage = 'Tarjeta en proceso.';
                optimisticInProgress = true;
            }

            if (targetStatus === 'complete') {
                url = completeUrl;
                params = { card_id: card.dataset.cardId, completed: '1' };
                successMessage = 'Tarjeta completada.';
                optimisticCompleted = true;
            }

            setCardState(card, optimisticCompleted, optimisticInProgress);
            statusButtons.forEach(button => {
                button.disabled = true;
            });

            try {
                const data = await postForm(url, params);
                setCardState(card, !!data.completed, !!data.in_progress);
                syncBoardVersion(data);
                showStatus(successMessage, 'ok');
                showViewMessage(successMessage, 'ok');
            } catch (error) {
                setCardState(card, previousCompleted, previousInProgress);
                showStatus(error.message, 'error');
                showViewMessage(error.message, 'error');
            } finally {
                syncViewState(card);
            }
        }

        if (viewPendingButton) {
            viewPendingButton.addEventListener('click', () => updateActiveViewStatus('pending'));
        }

        if (viewProgressButton) {
            viewProgressButton.addEventListener('click', () => updateActiveViewStatus('progress'));
        }

        if (viewCompleteButton) {
            viewCompleteButton.addEventListener('click', () => updateActiveViewStatus('complete'));
        }

        if (viewCommentForm) {
            viewCommentForm.addEventListener('submit', event => {
                event.preventDefault();
                submitTraceForm(viewCommentForm);
            });

            const commentTextarea = viewCommentForm.querySelector('textarea[name="body"]');
            if (commentTextarea) {
                commentTextarea.addEventListener('keydown', event => {
                    if (event.key !== 'Enter' || event.shiftKey) return;

                    event.preventDefault();
                    if (commentTextarea.value.trim() === '' || viewCommentForm.dataset.submitting === '1') {
                        return;
                    }

                    if (typeof viewCommentForm.requestSubmit === 'function') {
                        viewCommentForm.requestSubmit();
                    } else {
                        viewCommentForm.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                    }
                });
            }
        }

        if (commentsChatForm) {
            commentsChatForm.addEventListener('submit', event => {
                event.preventDefault();
                submitTraceForm(commentsChatForm);
            });

            const chatTextarea = commentsChatForm.querySelector('textarea[name="body"]');
            if (chatTextarea) {
                chatTextarea.addEventListener('input', updateCommentsChatSendState);

                chatTextarea.addEventListener('keydown', event => {
                    if (event.key !== 'Enter' || event.shiftKey) return;

                    event.preventDefault();
                    if (chatTextarea.value.trim() === '' || commentsChatForm.dataset.submitting === '1') {
                        return;
                    }

                    if (typeof commentsChatForm.requestSubmit === 'function') {
                        commentsChatForm.requestSubmit();
                    } else {
                        commentsChatForm.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                    }
                });

                updateCommentsChatSendState();
            }
        }

        if (replyCancel) {
            replyCancel.addEventListener('click', clearReplyTarget);
        }

        if (chatReplyCancel) {
            chatReplyCancel.addEventListener('click', clearChatReplyTarget);
        }

        if (viewComments) {
            viewComments.addEventListener('click', event => {
                const button = event.target.closest('.board-comment-reply');
                if (!button) return;

                setReplyTarget(
                    button.dataset.replyCommentId || '',
                    button.dataset.replyAuthor || 'Usuario',
                    button.dataset.replyBody || ''
                );
            });
        }

        if (commentsChatBody) {
            commentsChatBody.addEventListener('click', event => {
                const button = event.target.closest('.board-comment-reply');
                if (!button) return;

                setChatReplyTarget(
                    button.dataset.replyCommentId || '',
                    button.dataset.replyAuthor || 'Usuario',
                    button.dataset.replyBody || ''
                );
            });
        }

        viewAttachmentForms.forEach(form => {
            form.addEventListener('submit', event => {
                event.preventDefault();
                submitTraceForm(form);
            });
        });

        [viewWorkAttachments, viewCompletionAttachments].forEach(container => {
            if (!container) return;

            container.addEventListener('click', event => {
                const deleteButton = event.target.closest('[data-delete-attachment]');
                if (deleteButton) {
                    event.preventDefault();

                    openConfirmAction({
                        title: 'Eliminar adjunto',
                        message: `Se eliminara "${deleteButton.dataset.deleteName || 'este adjunto'}" de la tarjeta. Si fue cargado por error, podras subir el archivo correcto despues.`,
                        action: 'Eliminar'
                    }, () => deleteAttachment(deleteButton));

                    return;
                }

                const button = event.target.closest('[data-preview-attachment]');
                if (!button) return;

                event.preventDefault();
                openAttachmentPreview(
                    button.dataset.previewUrl || '',
                    button.dataset.downloadUrl || '',
                    button.dataset.previewName || 'Adjunto',
                    button.dataset.previewMime || ''
                );
            });
        });

        function openConfirmModal(form) {
            if (!confirmModal || !form) return;

            pendingConfirmForm = form;
            pendingConfirmCallback = null;

            if (confirmTitle) {
                confirmTitle.textContent = form.dataset.confirmTitle || 'Confirmar accion';
            }

            if (confirmMessage) {
                confirmMessage.textContent = form.dataset.confirmMessage || 'Confirma para continuar.';
            }

            if (confirmAccept) {
                confirmAccept.textContent = form.dataset.confirmAction || 'Confirmar';
            }

            confirmModal.hidden = false;
            window.requestAnimationFrame(() => confirmAccept?.focus());
        }

        function openConfirmAction(options, callback) {
            if (!confirmModal || typeof callback !== 'function') {
                callback();
                return;
            }

            pendingConfirmForm = null;
            pendingConfirmCallback = callback;

            if (confirmTitle) {
                confirmTitle.textContent = options?.title || 'Confirmar accion';
            }

            if (confirmMessage) {
                confirmMessage.textContent = options?.message || 'Confirma para continuar.';
            }

            if (confirmAccept) {
                confirmAccept.textContent = options?.action || 'Confirmar';
            }

            confirmModal.hidden = false;
            window.requestAnimationFrame(() => confirmAccept?.focus());
        }

        function closeConfirmModal() {
            if (!confirmModal) return;
            confirmModal.hidden = true;
            pendingConfirmForm = null;
            pendingConfirmCallback = null;
        }

        document.querySelectorAll('form[data-board-confirm]').forEach(form => {
            form.addEventListener('submit', event => {
                if (form.dataset.confirmed === '1') {
                    delete form.dataset.confirmed;
                    return;
                }

                event.preventDefault();
                openConfirmModal(form);
            });
        });

        if (confirmAccept) {
            confirmAccept.addEventListener('click', () => {
                const form = pendingConfirmForm;
                const callback = pendingConfirmCallback;
                closeConfirmModal();

                if (callback) {
                    callback();
                    return;
                }

                if (form) {
                    form.dataset.confirmed = '1';
                    form.requestSubmit();
                }
            });
        }

        if (confirmCancel) {
            confirmCancel.addEventListener('click', () => closeConfirmModal());
        }

        if (confirmModal) {
            confirmModal.addEventListener('pointerdown', event => {
                if (event.target === confirmModal) {
                    closeConfirmModal();
                }
            });
        }

        if (previewModal) {
            previewModal.addEventListener('pointerdown', event => {
                if (event.target === previewModal) {
                    closeAttachmentPreview();
                }
            });
        }

        document.querySelectorAll('[data-preview-close]').forEach(button => {
            button.addEventListener('click', () => closeAttachmentPreview());
        });

        if (editModal) {
            editModal.addEventListener('pointerdown', event => {
                if (event.target === editModal) {
                    closeEditModal(false);
                }
            });
        }

        document.querySelectorAll('[data-edit-close]').forEach(button => {
            button.addEventListener('click', () => closeEditModal(true));
        });

        if (liveReloadButton) {
            liveReloadButton.addEventListener('click', () => {
                window.location.reload();
            });
        }

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                checkBoardVersion();
            }
        });

        document.addEventListener('keydown', event => {
            if (event.key !== 'Escape') return;

            if (commentsChatModal && !commentsChatModal.hidden) {
                closeCommentsChatModal();
                return;
            }

            if (descriptionModal && !descriptionModal.hidden) {
                closeDescriptionModal();
                return;
            }

            if (previewModal && !previewModal.hidden) {
                closeAttachmentPreview();
                return;
            }

            if (confirmModal && !confirmModal.hidden) {
                closeConfirmModal();
                return;
            }

            if (viewModal && !viewModal.hidden) {
                closeViewModal();
                return;
            }

            if (editModal && !editModal.hidden) {
                closeEditModal(false);
            }
        });

        document.querySelectorAll('.board-card').forEach(card => {
            card.addEventListener('click', event => {
                if (suppressCardOpen || draggedCard) return;
                if (event.target.closest('button, a, form, input, textarea, select, label, summary, details')) return;
                openViewModal(card);
            });

            card.addEventListener('dragstart', event => {
                suppressCardOpen = true;
                draggedCard = card;
                sourceContainer = card.parentElement;
                sourceNextSibling = card.nextElementSibling;
                card.classList.add('dragging');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', card.dataset.cardId);
            });

            card.addEventListener('dragend', () => {
                card.classList.remove('dragging');
                document.querySelectorAll('.board-list').forEach(list => list.classList.remove('drop-active'));
                draggedCard = null;
                sourceContainer = null;
                sourceNextSibling = null;
                updateCounts();
                window.setTimeout(() => {
                    suppressCardOpen = false;
                }, 140);
            });
        });

        document.querySelectorAll('details.board-add-card .board-form').forEach(form => {
            form.addEventListener('submit', event => {
                if (form.dataset.submitting === '1') {
                    event.preventDefault();
                    return;
                }

                form.dataset.submitting = '1';
                form.setAttribute('aria-busy', 'true');

                const submitButton = form.querySelector('button[type="submit"]');

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Creando...';
                }

                const details = form.closest('details.board-add-card');

                if (details) {
                    details.open = false;
                }
            });
        });

        document.querySelectorAll('.board-form').forEach(form => {
            const unassignedToggle = form.querySelector('[data-unassigned-toggle]');
            const withoutAssigneeInput = form.querySelector('[data-without-assignee]');
            const assigneeInputs = [...form.querySelectorAll('input[name="assignees[]"]')];

            if (!unassignedToggle || !withoutAssigneeInput) return;

            unassignedToggle.addEventListener('change', () => {
                if (unassignedToggle.checked) {
                    assigneeInputs.forEach(input => {
                        input.checked = false;
                    });
                    withoutAssigneeInput.value = '1';
                } else {
                    withoutAssigneeInput.value = '0';
                }
            });

            assigneeInputs.forEach(input => {
                input.addEventListener('change', () => {
                    if (input.checked) {
                        unassignedToggle.checked = false;
                        withoutAssigneeInput.value = '0';
                    }
                });
            });
        });

        document.querySelectorAll('.board-card-check').forEach(check => {
            check.addEventListener('pointerdown', event => {
                event.stopPropagation();
            });

            check.addEventListener('click', async event => {
                event.preventDefault();
                event.stopPropagation();

                const card = check.closest('.board-card');
                if (!card || check.disabled) return;

                const previousCompleted = card.dataset.cardComplete === '1';
                const previousInProgress = card.dataset.cardProgress === '1';
                const nextCompleted = !previousCompleted;

                check.disabled = true;
                setCardState(card, nextCompleted, false);

                try {
                    const data = await postForm(completeUrl, {
                        card_id: card.dataset.cardId,
                        completed: nextCompleted ? '1' : '0'
                    });
                    setCardState(card, !!data.completed, !!data.in_progress);
                    syncBoardVersion(data);
                    showStatus(data.completed ? 'Tarjeta completada.' : 'Tarjeta pendiente.', 'ok');
                } catch (error) {
                    setCardState(card, previousCompleted, previousInProgress);
                    showStatus(error.message, 'error');
                } finally {
                    check.disabled = false;
                }
            });
        });

        document.querySelectorAll('.board-card-progress').forEach(progress => {
            progress.addEventListener('pointerdown', event => {
                event.stopPropagation();
            });

            progress.addEventListener('click', async event => {
                event.preventDefault();
                event.stopPropagation();

                const card = progress.closest('.board-card');
                if (!card || progress.disabled) return;

                const previousCompleted = card.dataset.cardComplete === '1';
                const previousInProgress = card.dataset.cardProgress === '1';
                const nextInProgress = !previousInProgress;

                progress.disabled = true;
                setCardState(card, false, nextInProgress);

                try {
                    const data = await postForm(progressUrl, {
                        card_id: card.dataset.cardId,
                        in_progress: nextInProgress ? '1' : '0'
                    });
                    setCardState(card, !!data.completed, !!data.in_progress);
                    syncBoardVersion(data);
                    showStatus(data.in_progress ? 'Tarjeta en proceso.' : 'Tarjeta pendiente.', 'ok');
                } catch (error) {
                    setCardState(card, previousCompleted, previousInProgress);
                    showStatus(error.message, 'error');
                } finally {
                    progress.disabled = false;
                }
            });
        });

        document.querySelectorAll('.board-list').forEach(list => {
            list.addEventListener('dragover', event => {
                if (!draggedCard) return;
                event.preventDefault();
                list.classList.add('drop-active');

                const targetCards = list.querySelector('.board-cards');
                if (!targetCards) return;

                const afterElement = getDragAfterElement(targetCards, event.clientY);
                if (afterElement) {
                    targetCards.insertBefore(draggedCard, afterElement);
                } else {
                    targetCards.appendChild(draggedCard);
                }

                updateCounts();
            });

            list.addEventListener('dragleave', event => {
                if (!list.contains(event.relatedTarget)) {
                    list.classList.remove('drop-active');
                }
            });

            list.addEventListener('drop', async event => {
                event.preventDefault();
                list.classList.remove('drop-active');

                if (!draggedCard) return;

                const targetCards = list.querySelector('.board-cards');
                const targetListId = list.dataset.listId;
                const previousListId = draggedCard.dataset.listId;
                const previousPos = draggedCard.dataset.cardPos;
                const samePosition = previousListId === targetListId && sourceNextSibling === draggedCard.nextElementSibling;

                if (!targetCards || !targetListId || samePosition) {
                    draggedCard.dataset.listId = targetListId;
                    return;
                }

                const position = calculatePosition(draggedCard);
                draggedCard.dataset.listId = targetListId;
                draggedCard.dataset.cardPos = position === 'bottom' ? String(Date.now()) : position;
                updateCounts();

                try {
                    const data = await postForm(moveUrl, {
                        card_id: draggedCard.dataset.cardId,
                        list_id: targetListId,
                        position
                    });
                    draggedCard.dataset.listId = String(data.list_id || targetListId);
                    draggedCard.dataset.cardPos = String(data.position || draggedCard.dataset.cardPos);
                    syncBoardVersion(data);
                    showStatus('Tarjeta movida.', 'ok');
                } catch (error) {
                    draggedCard.dataset.listId = previousListId;
                    draggedCard.dataset.cardPos = previousPos;
                    if (sourceNextSibling && sourceNextSibling.parentElement === sourceContainer) {
                        sourceContainer.insertBefore(draggedCard, sourceNextSibling);
                    } else if (sourceContainer) {
                        sourceContainer.appendChild(draggedCard);
                    }
                    updateCounts();
                    showStatus(error.message, 'error');
                }
            });
        });

        updateCounts();
        window.setInterval(checkBoardVersion, 8000);
    })();
</script>
