import { test, chromium } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';

const baseUrl = 'http://172.19.0.3:8090';
const outDir = '/home/arismaulana/pencil-assets';

async function shot(page, name, url, options = {}) {
  await page.goto(`${baseUrl}${url}`, { waitUntil: 'networkidle' });
  if (options.waitMs) {
    await page.waitForTimeout(options.waitMs);
  }
  await page.screenshot({
    path: path.join(outDir, `${name}.png`),
    fullPage: true,
  });
}

async function login(page, email, password) {
  await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle' });
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await page.waitForLoadState('networkidle');
}

test('capture all dentalcare screens', async () => {
  await fs.mkdir(outDir, { recursive: true });

  const browser = await chromium.launch({
    executablePath: '/usr/bin/google-chrome',
    headless: true,
  });

  try {
    const guest = await browser.newContext({ viewport: { width: 1440, height: 1200 } });
    const guestPage = await guest.newPage();
    await shot(guestPage, 'public-home', '/');
    await shot(guestPage, 'public-services', '/services');
    await shot(guestPage, 'public-doctors', '/doctors');
    await shot(guestPage, 'public-booking', '/booking/create');
    await shot(guestPage, 'auth-login', '/login');
    await shot(guestPage, 'auth-register', '/register');
    await shot(guestPage, 'auth-forgot', '/forgot-password');
    await shot(guestPage, 'auth-reset', '/reset-password/demo-token');
    await guest.close();

    const patient = await browser.newContext({ viewport: { width: 1440, height: 1200 } });
    const patientPage = await patient.newPage();
    await login(patientPage, 'patient@dentalcare.test', 'password');
    await shot(patientPage, 'auth-verify', '/verify-email');
    await shot(patientPage, 'auth-confirm', '/confirm-password');
    await shot(patientPage, 'patient-dashboard', '/dashboard');
    await shot(patientPage, 'patient-history', '/history');
    await shot(patientPage, 'patient-booking', '/booking/create');
    await shot(patientPage, 'profile-edit', '/profile');
    await patient.close();

    const doctor = await browser.newContext({ viewport: { width: 1440, height: 1200 } });
    const doctorPage = await doctor.newPage();
    await login(doctorPage, 'dr.aji@dentalcare.test', 'password');
    await shot(doctorPage, 'doctor-dashboard', '/doctor/dashboard');
    await shot(doctorPage, 'doctor-notes', '/doctor/medical-notes');
    await shot(doctorPage, 'doctor-note-form', '/doctor/medical-notes/1');
    await doctor.close();

    const admin = await browser.newContext({ viewport: { width: 1440, height: 1200 } });
    const adminPage = await admin.newPage();
    await login(adminPage, 'admin@dentalcare.test', 'password');
    await shot(adminPage, 'admin-reports', '/admin/reports', { waitMs: 1500 });
    await shot(adminPage, 'admin-payments', '/admin/payments');
    await shot(adminPage, 'admin-services', '/admin/services');
    await shot(adminPage, 'admin-service-form', '/admin/services/1/edit');
    await shot(adminPage, 'admin-doctors', '/admin/doctors');
    await shot(adminPage, 'admin-doctor-form', '/admin/doctors/3/edit');
    await shot(adminPage, 'admin-schedules', '/admin/schedules');
    await shot(adminPage, 'admin-schedule-form', '/admin/schedules/1/edit');
    await admin.close();
  } finally {
    await browser.close();
  }
});
