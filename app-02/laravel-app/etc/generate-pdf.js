const puppeteer = require('puppeteer');
const argv = require('yargs').argv;

(async () => {
  try {
  	const browser = await puppeteer.launch();
    const page = await browser.newPage();

    await page.goto(argv.url, {waitUntil: 'networkidle2'});
    await page.pdf({
        landscape: true,
    	path: argv.filename,
    	format: 'A4',
    	printBackground: true,
   		displayHeaderFooter: false,
	});
    await browser.close();
  } catch (e) {
    console.log( e);
  }
})();