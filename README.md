# Omniful

Omniful Core Plugin is a basic set up for building an admin facing page in React.js. ESbuild (a fast next-generation JavaScript bundler) has been used as a bundler.

# Compatibility

Requires PHP 5.6 or higher.

## Get started

1. Get a copy the of plugin, either by downloading from WordPress plugins repository or simply cloning git repository of the plugin.

2. The downloaded plugin will have `omniful-admin` folder name. Rename it to your plugin name, also rename the file `omniful-admin.php` to match your folder name.

3. Change the plugin information (in `omniful-admin.php`) according to your needs.

4. Go to `src/react` directory and install dependencies by running `yarn`.

5. Build commands
- `yarn build` - Production build
- `yarn build:dev` - Development build, generates sourcemaps
- `yarn watch` - Automatically runs `yarn build:dev` when files change

## Note

Do not modify anything outside `src` in `react` folder if you are not confident.