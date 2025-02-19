<?php

namespace Fruitcake\TelescopeToolbar\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Laravel\Telescope\Contracts\EntriesRepository;
use Laravel\Telescope\Storage\EntryQueryOptions;
use Laravel\Telescope\Telescope;

class ToolbarController extends Controller
{
    /**
     * @var \Laravel\Telescope\Contracts\EntriesRepository
     */
    private $entriesRepository;

    public function __construct(EntriesRepository $entriesRepository)
    {
        $this->entriesRepository = $entriesRepository;
    }

    public function render($token)
    {
        Telescope::stopRecording();

        $this->prepareBlade($token);

        $options = $this->findBatchOptions($token);

        $entries = $this->entriesRepository->get(null, $options)->groupBy('type');

        return View::make('telescope-toolbar::toolbar', [
            'entries' => $entries,
        ]);
    }

    public function show($token)
    {
        Telescope::stopRecording();

        $options = $this->findBatchOptions($token);

        $request = $this->entriesRepository->get('request', $options)->first();

        return redirect(route('telescope') . '/requests/' . $request->id);
    }

    /**
     * Make sure Blade has the correct Directives and shares the Token
     *
     * @param $token
     */
    private function prepareBlade($token)
    {
        View::share('token', $token);

        Blade::directive('ttIcon', function ($expression) {
            $dir = realpath(__DIR__ . '/../../../resources/icons');
            return "<?php echo file_get_contents('$dir/' . basename($expression) . '.svg'); ?>";
        });

    }

    /**
     * Find the search options for the related entries.
     *
     * @param $token
     *
     * @return \Laravel\Telescope\Storage\EntryQueryOptions
     */
    private function findBatchOptions($token) : EntryQueryOptions
    {
        $entry = $this->entriesRepository->find($token);

        return EntryQueryOptions::forBatchId($entry->batchId)->limit(-1);
    }
}