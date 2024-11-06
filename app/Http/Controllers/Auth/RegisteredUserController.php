<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_image' => $this->generateInitialImage($request->name),
            'is_new' => true,
        ]);
               
        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    protected function generateInitialImage($name)
    {
        // Extract initials from the name
        $initials = implode('', array_map(function($word) {
            return strtoupper(substr($word, 0, 1));
        }, explode(' ', trim($name))));

        // Set image dimensions
        $width = 150;
        $height = 150;

        // Create the image
        $image = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($image, 22, 50, 91); // Background color (Deep Ocean Blue)
        $textColor = imagecolorallocate($image, 255, 255, 255); // Text color (White)

        // Fill the background
        imagefill($image, 0, 0, $bgColor);

        // Set the path to your font
        $fontPath = public_path('fonts/Poppins/Poppins-SemiBold.ttf'); // Adjust the font path
        $fontSize = 50;

        // Calculate text bounding box
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $initials);
        $textWidth = abs($bbox[2] - $bbox[0]); // Width of the text
        $textHeight = abs($bbox[1] - $bbox[7]); // Height of the text

        // Calculate coordinates to center the text
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2 + $textHeight; // Adjust y to baseline
        // Add the initials to the image
        imagettftext($image, $fontSize, 0, $x - 8.5, $y, $textColor, $fontPath, $initials);


        $directoryPath = 'profile_image';
        if (!Storage::disk('public')->exists($directoryPath)) {
            Storage::disk('public')->makeDirectory($directoryPath); // Create the directory
        }

        // Save the image to the directory
        $fileName = uniqid() . '.png'; // Unique filename
        $filePath = $directoryPath . '/' . $fileName;
        
        // Use output buffering to capture the image data
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();

        // Store the image in the storage folder
        Storage::disk('public')->put($filePath, $imageData); // Save the image

        // Free up memory
        imagedestroy($image);

        // Return the path to the saved image
        return 'storage/' . $filePath;
    }



}
