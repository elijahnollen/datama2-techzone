# 📤 GitHub Upload Guide - Step by Step

## ✅ Files to Upload (Include These)

### **Core Application Files**
```
✅ src/
   ├── App.tsx
   ├── Root.tsx
   ├── routes.tsx
   ├── main.tsx
   ├── components/
   │   ├── Header.tsx
   │   ├── ProductCard.tsx
   │   ├── ReviewCard.tsx
   │   ├── AuthModal.tsx
   │   ├── DatabaseStatusBanner.tsx
   │   ├── figma/
   │   │   └── ImageWithFallback.tsx
   │   └── ui/ (all files)
   ├── contexts/
   │   ├── AuthContext.tsx
   │   └── CartContext.tsx
   ├── pages/
   │   ├── Home.tsx
   │   ├── ProductResults.tsx
   │   ├── ProductDetail.tsx
   │   ├── Cart.tsx
   │   ├── Checkout.tsx
   │   ├── Login.tsx
   │   ├── SignUp.tsx
   │   ├── Account.tsx
   │   ├── Profile.tsx
   │   ├── Wallet.tsx
   │   ├── Messages.tsx
   │   ├── MyOrders.tsx
   │   ├── ReturnRequest.tsx
   │   └── Contact.tsx
   ├── services/
   │   └── database.ts
   ├── types/
   │   └── index.ts
   ├── imports/ (all Figma design files)
   └── styles/
       └── globals.css
```

### **Configuration Files**
```
✅ package.json
✅ tsconfig.json
✅ vite.config.ts
✅ index.html
✅ .gitignore
```

### **Documentation**
```
✅ README.md
✅ GITHUB_UPLOAD_GUIDE.md (this file)
```

---

## ❌ Files to Exclude (Already in .gitignore)

```
❌ node_modules/        # Dependencies (too large, reinstall with npm install)
❌ dist/                # Build output (generated)
❌ build/               # Build output (generated)
❌ .env                 # Environment variables (secrets)
❌ .DS_Store            # Mac system file
❌ *.log                # Log files
❌ .cache/              # Cache files
```

---

## 🚀 Step-by-Step Upload to GitHub

### **Step 1: Prepare Your Project Folder**

1. Create a new folder on your computer called `techzone-frontend`
2. Copy ALL these folders and files from Figma Make to your folder:

```
techzone-frontend/
├── components/         (copy entire folder)
├── contexts/          (copy entire folder)
├── pages/             (copy entire folder)
├── services/          (copy entire folder)
├── imports/           (copy entire folder)
├── styles/            (copy entire folder)
├── types/             (copy entire folder)
├── App.tsx
├── Root.tsx
├── routes.tsx
├── index.html
├── package.json
├── tsconfig.json
├── vite.config.ts
├── .gitignore
└── README.md
```

3. Create a `src` folder and move these into it:
   - Move `App.tsx` → `src/App.tsx`
   - Move `Root.tsx` → `src/Root.tsx`
   - Move `routes.tsx` → `src/routes.tsx`
   - Move `components/` → `src/components/`
   - Move `contexts/` → `src/contexts/`
   - Move `pages/` → `src/pages/`
   - Move `services/` → `src/services/`
   - Move `imports/` → `src/imports/`
   - Move `styles/` → `src/styles/`
   - Move `types/` → `src/types/`
   - Add the `main.tsx` file to `src/main.tsx`

**Final structure should look like:**
```
techzone-frontend/
├── src/
│   ├── main.tsx
│   ├── App.tsx
│   ├── Root.tsx
│   ├── routes.tsx
│   ├── components/
│   ├── contexts/
│   ├── pages/
│   ├── services/
│   ├── imports/
│   ├── styles/
│   └── types/
├── index.html
├── package.json
├── tsconfig.json
├── vite.config.ts
├── .gitignore
└── README.md
```

---

### **Step 2: Install Git (if not already installed)**

**Windows:**
1. Download Git from https://git-scm.com/download/win
2. Install with default settings
3. Open "Git Bash" or "Command Prompt"

**Mac:**
1. Open Terminal
2. Type: `git --version`
3. If not installed, it will prompt you to install

**Linux:**
```bash
sudo apt-get install git
```

---

### **Step 3: Create a GitHub Repository**

1. Go to https://github.com
2. Log in to your account
3. Click the **"+"** button (top right) → **"New repository"**
4. Fill in:
   - **Repository name**: `techzone-frontend`
   - **Description**: "TechZone E-commerce Frontend - React & TypeScript"
   - **Visibility**: Choose "Public" or "Private"
   - **DO NOT** check "Add a README file" (we already have one)
   - **DO NOT** add .gitignore (we already have one)
5. Click **"Create repository"**
6. **COPY** the repository URL (looks like: `https://github.com/YOUR_USERNAME/techzone-frontend.git`)

---

### **Step 4: Initialize Git in Your Project**

1. Open **Terminal** (Mac/Linux) or **Git Bash** (Windows)
2. Navigate to your project folder:
   ```bash
   cd path/to/techzone-frontend
   ```
   
   Example:
   ```bash
   # Windows
   cd C:\Users\YourName\Desktop\techzone-frontend
   
   # Mac/Linux
   cd ~/Desktop/techzone-frontend
   ```

3. Initialize Git:
   ```bash
   git init
   ```

4. Add all files:
   ```bash
   git add .
   ```

5. Commit files:
   ```bash
   git commit -m "Initial commit: TechZone frontend with all features"
   ```

---

### **Step 5: Connect to GitHub and Push**

1. Add your GitHub repository as remote (replace with YOUR URL from Step 3):
   ```bash
   git remote add origin https://github.com/YOUR_USERNAME/techzone-frontend.git
   ```

2. Rename branch to main (if needed):
   ```bash
   git branch -M main
   ```

3. Push to GitHub:
   ```bash
   git push -u origin main
   ```

4. Enter your GitHub credentials when prompted:
   - **Username**: Your GitHub username
   - **Password**: Your GitHub Personal Access Token
     (If you don't have a token, see Step 6 below)

---

### **Step 6: Create GitHub Personal Access Token (if needed)**

If GitHub asks for a password and it doesn't work:

1. Go to https://github.com/settings/tokens
2. Click **"Generate new token"** → **"Generate new token (classic)"**
3. Name it: `techzone-upload`
4. Select scopes: ✅ **repo** (check all repo boxes)
5. Click **"Generate token"**
6. **COPY the token** (you won't see it again!)
7. Use this token as your password when pushing

---

### **Step 7: Verify Upload**

1. Go to your GitHub repository: `https://github.com/YOUR_USERNAME/techzone-frontend`
2. You should see all your files listed
3. Check that the README.md displays correctly

---

## 🔄 Making Changes Later

When you make changes to your code:

```bash
# 1. Check what changed
git status

# 2. Add changed files
git add .

# 3. Commit with a message
git commit -m "Added new feature"

# 4. Push to GitHub
git push
```

---

## 📦 Installing on Another Computer

Someone else (or you on another computer) can download and run your project:

```bash
# 1. Clone the repository
git clone https://github.com/YOUR_USERNAME/techzone-frontend.git

# 2. Navigate into folder
cd techzone-frontend

# 3. Install dependencies
npm install

# 4. Run development server
npm run dev
```

---

## ✅ Quick Checklist

Before uploading, make sure:
- [ ] `.gitignore` file exists
- [ ] `node_modules/` folder is NOT in your upload folder (it should be excluded by .gitignore)
- [ ] All files are in `src/` folder except config files
- [ ] `package.json` exists
- [ ] `README.md` exists
- [ ] You've committed all files with `git add .`
- [ ] You've pushed with `git push`

---

## 🆘 Common Issues

### **Issue 1: "node_modules is too large"**
**Solution**: Make sure `.gitignore` exists and includes `node_modules/`

### **Issue 2: "Permission denied (publickey)"**
**Solution**: Use HTTPS URL instead of SSH, or set up SSH keys

### **Issue 3: "Failed to push some refs"**
**Solution**: Pull first: `git pull origin main`, then push again

### **Issue 4: "Not a git repository"**
**Solution**: Make sure you ran `git init` in the correct folder

---

## 📞 Need Help?

- GitHub Documentation: https://docs.github.com
- Git Basics: https://git-scm.com/doc
- Git Tutorial: https://www.atlassian.com/git/tutorials

---

**🎉 Congratulations! Your frontend is now on GitHub!**
