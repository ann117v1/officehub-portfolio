<?php

namespace OfficeHub\Controllers;

use OfficeHub\Core\Controller;
use OfficeHub\Core\Response;
use OfficeHub\Services\TrelloService;
use Throwable;

class TrelloController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireAuth();

        $trello = TrelloService::fromConfig();
        $selectedBoardId = trim((string)$this->request->query('board', $trello->defaultBoardId()));
        $boards = [];
        $snapshot = [
            'board' => null,
            'lists' => [],
            'cardsByList' => [],
            'cards' => [],
        ];
        $error = null;

        if ($trello->isConfigured()) {
            try {
                $boards = $trello->boards();

                if ($selectedBoardId === '' && !empty($boards[0]['id'])) {
                    $selectedBoardId = (string)$boards[0]['id'];
                }

                if ($selectedBoardId !== '') {
                    $snapshot = $trello->boardSnapshot($selectedBoardId);
                }
            } catch (Throwable $exception) {
                $error = $exception->getMessage();
            }
        }

        $this->view('trello/index', [
            'title' => 'Tablero Trello',
            'isConfigured' => $trello->isConfigured(),
            'selectedBoardId' => $selectedBoardId,
            'boards' => $boards,
            'board' => $snapshot['board'],
            'lists' => $snapshot['lists'],
            'cardsByList' => $snapshot['cardsByList'],
            'cards' => $snapshot['cards'],
            'error' => $error,
        ]);
    }

    public function moveCard(array $params = []): void
    {
        $this->requireAuth();

        $cardId = trim((string)$this->request->input('card_id', ''));
        $listId = trim((string)$this->request->input('list_id', ''));
        $position = trim((string)$this->request->input('position', 'bottom'));

        if ($cardId === '' || $listId === '') {
            $this->json([
                'ok' => false,
                'message' => 'Faltan datos para mover la tarjeta.',
            ], 422);
        }

        try {
            $card = TrelloService::fromConfig()->moveCard($cardId, $listId, $position);

            $this->json([
                'ok' => true,
                'list_id' => $card['idList'] ?? $listId,
                'position' => $card['pos'] ?? $position,
            ]);
        } catch (Throwable $exception) {
            $this->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function completeCard(array $params = []): void
    {
        $this->requireAuth();

        $cardId = trim((string)$this->request->input('card_id', ''));
        $completed = (string)$this->request->input('completed', '0') === '1';

        if ($cardId === '') {
            $this->json([
                'ok' => false,
                'message' => 'Falta la tarjeta para actualizar.',
            ], 422);
        }

        try {
            $card = TrelloService::fromConfig()->updateCardComplete($cardId, $completed);

            $this->json([
                'ok' => true,
                'completed' => !empty($card['dueComplete']),
            ]);
        } catch (Throwable $exception) {
            $this->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
