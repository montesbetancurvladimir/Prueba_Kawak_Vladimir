<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Core\Security\Auth;
use App\Core\Security\Csrf;
use App\Core\View;
use App\Http\Request;
use App\Http\Response;
use App\Repositories\DocumentRepository;
use App\Repositories\ProcessRepository;
use App\Repositories\TypeRepository;

final class DocumentController extends BaseController
{
    /** @var DocumentRepository */
    private $documents;
    /** @var TypeRepository */
    private $types;
    /** @var ProcessRepository */
    private $processes;

    public function __construct(
        View $view,
        Auth $auth,
        Csrf $csrf,
        Flash $flash,
        DocumentRepository $documents,
        TypeRepository $types,
        ProcessRepository $processes
    ) {
        parent::__construct($view, $auth, $csrf, $flash);
        $this->documents = $documents;
        $this->types = $types;
        $this->processes = $processes;
    }

    /**
     * @param array<string, string> $params
     */
    public function index(Request $request, array $params): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $q = $request->queryString('q');
        $rows = $this->documents->list($q);

        return $this->render('documents/index.php', [
            'q' => $q,
            'rows' => $rows,
        ]);
    }

    /**
     * @param array<string, string> $params
     */
    public function createForm(Request $request, array $params): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        return $this->render('documents/form.php', [
            'mode' => 'create',
            'doc' => ['DOC_NOMBRE' => '', 'DOC_CONTENIDO' => '', 'DOC_ID_TIPO' => 0, 'DOC_ID_PROCESO' => 0],
            'types' => $this->types->all(),
            'processes' => $this->processes->all(),
            'error' => '',
        ]);
    }

    /**
     * @param array<string, string> $params
     */
    public function create(Request $request, array $params): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }
        if (!$this->csrf->verify($request->postString('_csrf'))) {
            return $this->badRequest('CSRF inválido');
        }

        $nombre = $request->postString('DOC_NOMBRE');
        $contenido = $request->postString('DOC_CONTENIDO');
        $tipoId = $request->postInt('DOC_ID_TIPO');
        $procesoId = $request->postInt('DOC_ID_PROCESO');

        try {
            $this->documents->create($nombre, $contenido, $tipoId, $procesoId);
            $this->flash->set('success', 'Documento creado');
            return Response::redirect('/documents');
        } catch (\Throwable $e) {
            return $this->render('documents/form.php', [
                'mode' => 'create',
                'doc' => ['DOC_NOMBRE' => $nombre, 'DOC_CONTENIDO' => $contenido, 'DOC_ID_TIPO' => $tipoId, 'DOC_ID_PROCESO' => $procesoId],
                'types' => $this->types->all(),
                'processes' => $this->processes->all(),
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * @param array<string, string> $params
     */
    public function editForm(Request $request, array $params): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = isset($params['id']) && preg_match('/^\d+$/', $params['id']) ? (int) $params['id'] : 0;
        $doc = $this->documents->find($id);
        if ($doc === null) {
            return new Response('Not found', 404, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        return $this->render('documents/form.php', [
            'mode' => 'edit',
            'doc' => $doc,
            'types' => $this->types->all(),
            'processes' => $this->processes->all(),
            'error' => '',
        ]);
    }

    /**
     * @param array<string, string> $params
     */
    public function update(Request $request, array $params): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }
        if (!$this->csrf->verify($request->postString('_csrf'))) {
            return $this->badRequest('CSRF inválido');
        }

        $id = isset($params['id']) && preg_match('/^\d+$/', $params['id']) ? (int) $params['id'] : 0;
        $nombre = $request->postString('DOC_NOMBRE');
        $contenido = $request->postString('DOC_CONTENIDO');
        $tipoId = $request->postInt('DOC_ID_TIPO');
        $procesoId = $request->postInt('DOC_ID_PROCESO');

        try {
            $this->documents->update($id, $nombre, $contenido, $tipoId, $procesoId);
            $this->flash->set('success', 'Documento actualizado');
            return Response::redirect('/documents');
        } catch (\Throwable $e) {
            $doc = $this->documents->find($id) ?? ['DOC_ID' => $id, 'DOC_CODIGO' => '', 'DOC_CONSECUTIVO' => 0];
            $doc['DOC_NOMBRE'] = $nombre;
            $doc['DOC_CONTENIDO'] = $contenido;
            $doc['DOC_ID_TIPO'] = $tipoId;
            $doc['DOC_ID_PROCESO'] = $procesoId;

            return $this->render('documents/form.php', [
                'mode' => 'edit',
                'doc' => $doc,
                'types' => $this->types->all(),
                'processes' => $this->processes->all(),
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * @param array<string, string> $params
     */
    public function delete(Request $request, array $params): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }
        if (!$this->csrf->verify($request->postString('_csrf'))) {
            return $this->badRequest('CSRF inválido');
        }

        $id = isset($params['id']) && preg_match('/^\d+$/', $params['id']) ? (int) $params['id'] : 0;
        $this->documents->delete($id);
        $this->flash->set('success', 'Documento eliminado');
        return Response::redirect('/documents');
    }
}

