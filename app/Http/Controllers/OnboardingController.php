<?php

namespace App\Http\Controllers;

use App\Contracts\Services\OnboardingServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class OnboardingController extends Controller
{
    protected $onboardingService;

    public function __construct(OnboardingServiceInterface $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    /**
     * Tampilkan wizard onboarding.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Onboarding/Index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Proses onboarding user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'workspace_name' => 'required|string|max:100',
            'workspace_description' => 'nullable|string',
            'create_default_project' => 'required|boolean',
            'project_name' => 'required_if:create_default_project,true|nullable|string|max:100',
            'project_description' => 'nullable|string',
        ]);

        $this->onboardingService->completeOnboarding($request->user(), $validated);

        return redirect('/dashboard');
    }
}
