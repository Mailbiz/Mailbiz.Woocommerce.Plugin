const fs = require("fs-extra");
const archiver = require("archiver");
const path = require("path");

// Use project root as base directory
const projectRoot = path.resolve(__dirname, "..");

async function clean() {
  const tmpDir = path.join(projectRoot, "tmp");
  const distDir = path.join(projectRoot, "build");

  try {
    await Promise.all([
      fs.pathExists(tmpDir).then((exists) => exists && fs.remove(tmpDir)),
      fs.pathExists(distDir).then((exists) => exists && fs.remove(distDir)),
    ]);
    console.log("Clean completed successfully");
  } catch (error) {
    console.error("Clean failed:", error);
    process.exit(1);
  }
}

async function build() {
  const tmpDir = path.join(projectRoot, "tmp");
  const outputZip = path.join(projectRoot, "build", "mailbiz-tracker-for-woocommerce.zip");
  const srcDir = path.join(projectRoot, "src");
  const additionalFiles = ["LICENSE", "readme.txt"];

  try {
    // Clean before building
    await clean();

    // Create necessary directories
    await fs.ensureDir(path.dirname(outputZip));
    await fs.ensureDir(tmpDir);

    console.log("Copying files...");

    // Copy src directory to tmp
    await fs.copy(srcDir, tmpDir);

    // Copy additional files to tmp
    for (const file of additionalFiles) {
      const sourcePath = path.join(projectRoot, file);
      if (await fs.pathExists(sourcePath)) {
        await fs.copy(sourcePath, path.join(tmpDir, file));
      } else {
        console.warn(`Warning: ${file} not found, skipping...`);
      }
    }

    console.log("Creating zip file...");

    // Create zip file
    const output = fs.createWriteStream(outputZip);
    const archive = archiver("zip", {
      zlib: { level: 0 }, // No compression for fastest processing
    });

    archive.pipe(output);

    // Wait for zip to finish
    await new Promise((resolve, reject) => {
      output.on("close", () => {
        console.log(`Zip file created: ${outputZip}`);
        resolve();
      });
      output.on("error", reject);
      archive.on("error", reject);
      archive.on("warning", (warn) => {
        console.warn(warn);
      });

      archive.directory(tmpDir, false);
      archive.finalize();
    });

    // Clean up tmp directory
    console.log("Cleaning up temporary files...");
    await fs.remove(tmpDir);

    console.log("Build completed successfully!");
  } catch (error) {
    console.error("Build failed:", error);
    // Clean up tmp directory if it exists
    if (await fs.pathExists(tmpDir)) {
      await fs.remove(tmpDir);
    }
    process.exit(1);
  }
}

// Main execution
async function main() {
  try {
    await build();
  } catch (error) {
    console.error("An unexpected error occurred:", error);
    process.exit(1);
  }
}

// Run the script
main();
