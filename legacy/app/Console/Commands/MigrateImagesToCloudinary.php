<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class MigrateImagesToCloudinary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudinary:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload local images to Cloudinary and update database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting image migration to Cloudinary...");

        $cloudinaryUrl = env('CLOUDINARY_URL');
        if ($cloudinaryUrl) {
            config(['cloudinary.cloud_url' => $cloudinaryUrl]);
        }

        $this->migrateTable('produk', 'id_produk', 'gambar', 'produk');
        $this->migrateTable('kategori', 'id_kategori', 'gambar', 'kategori');
        $this->migrateTable('sliders', 'id_slider', 'gambar', 'sliders');
        $this->migrateTable('user', 'id_user', 'foto_profil', 'profiles');

        $this->info("Migration completed!");
    }

    private function migrateTable($table, $primaryKey, $imageColumn, $folder)
    {
        $this->info("Checking table: $table...");
        $records = DB::table($table)->whereNotNull($imageColumn)->get();

        foreach ($records as $record) {
            $imagePath = $record->$imageColumn;

            // Skip if it's already a URL
            if (empty($imagePath) || str_starts_with($imagePath, 'http')) {
                continue;
            }

            $localFilePath = storage_path("app/public/" . $imagePath);

            if (file_exists($localFilePath)) {
                $this->info("Uploading $imagePath to Cloudinary...");
                try {
                    $uploadResult = Cloudinary::uploadApi()->upload($localFilePath, [
                        'folder' => $folder
                    ]);
                    $uploadedFileUrl = $uploadResult['secure_url'];

                    DB::table($table)
                        ->where($primaryKey, $record->$primaryKey)
                        ->update([$imageColumn => $uploadedFileUrl]);

                    $this->info("Success! Updated $table ID: " . $record->$primaryKey);
                } catch (\Exception $e) {
                    $this->error("Failed to upload $imagePath: " . $e->getMessage());
                }
            } else {
                $this->warn("Local file not found: $localFilePath");
            }
        }
    }
}
