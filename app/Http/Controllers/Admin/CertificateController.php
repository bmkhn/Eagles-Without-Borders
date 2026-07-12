<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CertificateController extends Controller
{
    /**
     * Store a newly created certificate for a member.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'name' => ['required', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        $member = Member::findOrFail($validated['member_id']);

        // Scope check
        $user = request()->user();
        if ($user->hasRole('club-admin') && $user->club_id && (int) $member->club_id !== (int) $user->club_id) {
            abort(403, 'You can only manage certificates for members in your club.');
        }
        if ($user->hasRole('regional-admin') && $user->region_id && $member->club) {
            $memberRegionId = $member->club->region_id;
            if ((int) $memberRegionId !== (int) $user->region_id) {
                abort(403, 'You can only manage certificates for members in your region.');
            }
        }

        $data = [
            'member_id' => $member->id,
            'name' => $validated['name'],
            'issued_at' => $validated['issued_at'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $data['file'] = $this->storeCertificateFile($request->file('file'));
        }

        $certificate = Certificate::create($data);

        activity()
            ->performedOn($certificate)
            ->causedBy(auth()->user())
            ->withProperties([
                'member_id' => $member->id,
                'member_name' => $member->name,
                'certificate_name' => $certificate->name,
            ])
            ->log('certificate_added');

        return redirect()
            ->route('admin.members.edit', $member)
            ->with('success', "Certificate '{$certificate->name}' added successfully.");
    }

    /**
     * Update an existing certificate.
     */
    public function update(Request $request, Certificate $certificate): RedirectResponse
    {
        $member = $certificate->member;

        // Scope check
        $user = request()->user();
        if ($user->hasRole('club-admin') && $user->club_id && $member && (int) $member->club_id !== (int) $user->club_id) {
            abort(403, 'You can only manage certificates for members in your club.');
        }
        if ($user->hasRole('regional-admin') && $user->region_id && $member && $member->club) {
            $memberRegionId = $member->club->region_id;
            if ((int) $memberRegionId !== (int) $user->region_id) {
                abort(403, 'You can only manage certificates for members in your region.');
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        $changes = [];
        if ($certificate->name !== $validated['name']) {
            $changes['name'] = ['old' => $certificate->name, 'new' => $validated['name']];
        }
        if (($certificate->issued_at?->format('Y-m-d') ?? '') !== ($validated['issued_at'] ?? '')) {
            $changes['issued_at'] = ['old' => $certificate->issued_at?->format('Y-m-d'), 'new' => $validated['issued_at'] ?? null];
        }

        $data = [
            'name' => $validated['name'],
            'issued_at' => $validated['issued_at'] ?? null,
        ];

        if ($request->hasFile('file')) {
            if ($certificate->file) {
                Storage::disk('public')->delete($certificate->file);
            }
            $data['file'] = $this->storeCertificateFile($request->file('file'));
            $changes['file'] = ['old' => '(previous)', 'new' => '(replaced)'];
        }

        $certificate->update($data);

        if (!empty($changes)) {
            activity()
                ->performedOn($certificate)
                ->causedBy(auth()->user())
                ->withProperties([
                    'member_id' => $member?->id,
                    'member_name' => $member?->name,
                    'certificate_name' => $certificate->name,
                    'changes' => $changes,
                ])
                ->log('certificate_updated');
        }

        return redirect()
            ->route('admin.members.edit', $member)
            ->with('success', "Certificate '{$certificate->name}' updated successfully.");
    }

    /**
     * Remove the specified certificate (soft delete).
     */
    public function destroy(Request $request, Certificate $certificate): RedirectResponse
    {
        $request->validate([
            'confirm_delete' => ['required', 'accepted'],
            'confirm_text' => ['required', 'string', 'in:DELETE'],
        ]);

        $member = $certificate->member;

        // Scope check
        $user = request()->user();
        if ($user->hasRole('club-admin') && $user->club_id && $member && (int) $member->club_id !== (int) $user->club_id) {
            abort(403, 'You can only manage certificates for members in your club.');
        }
        if ($user->hasRole('regional-admin') && $user->region_id && $member && $member->club) {
            $memberRegionId = $member->club->region_id;
            if ((int) $memberRegionId !== (int) $user->region_id) {
                abort(403, 'You can only manage certificates for members in your region.');
            }
        }

        $certName = $certificate->name;

        activity()
            ->performedOn($certificate)
            ->causedBy(auth()->user())
            ->withProperties([
                'member_id' => $member?->id,
                'member_name' => $member?->name,
                'certificate_name' => $certName,
            ])
            ->log('certificate_deleted');

        $certificate->delete(); // soft delete

        return redirect()
            ->route('admin.members.edit', $member)
            ->with('success', "Certificate '{$certName}' removed successfully.");
    }

    /**
     * Store a certificate file with optimization for images.
     */
    private function storeCertificateFile(UploadedFile $file): string
    {
        $mime = $file->getMimeType();

        if (str_starts_with($mime, 'image/')) {
            return $this->optimizeAndStoreImage($file, 'certificates', 1200, 1200, 70);
        }

        $extension = $file->getClientOriginalExtension() ?: 'pdf';
        $filename = uniqid('cert_') . '.' . $extension;

        return $file->storeAs('certificates', $filename, 'public');
    }

    /**
     * Store and optimize an uploaded image.
     */
    private function optimizeAndStoreImage(UploadedFile $file, string $directory, int $maxWidth = 1200, int $maxHeight = 1200, int $quality = 70): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->decode($file);

        $image->scale(width: $maxWidth, height: $maxHeight);

        $filename = uniqid('img_') . '.webp';
        $path = $directory . '/' . $filename;

        $encoded = $image->encode(new WebpEncoder(quality: $quality));
        Storage::disk('public')->put($path, $encoded);

        return $path;
    }
}
