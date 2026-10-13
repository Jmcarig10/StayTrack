import { chromium } from 'playwright';
const browser = await chromium.launch({headless:true});
const page = await browser.newPage({viewport:{width:1366,height:768}, deviceScaleFactor:1});
await page.goto('http://127.0.0.1:8000/', {waitUntil:'networkidle', timeout:60000});
await page.screenshot({path:'C:/xampp/htdocs/staytrack/.tmp/site-landing.png', fullPage:false});
await page.goto('http://127.0.0.1:8000/login', {waitUntil:'networkidle', timeout:60000});
await page.screenshot({path:'C:/xampp/htdocs/staytrack/.tmp/site-login.png', fullPage:false});
await browser.close();
