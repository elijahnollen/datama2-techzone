# ⚡ Git Command Reference - Copy & Paste

## 🚀 Initial Upload (First Time)

```bash
# 1. Navigate to your project folder
cd /path/to/techzone-frontend

# 2. Initialize Git repository
git init

# 3. Add all files to staging
git add .

# 4. Create first commit
git commit -m "Initial commit: TechZone e-commerce frontend with all features"

# 5. Connect to GitHub (REPLACE with your actual repository URL)
git remote add origin https://github.com/YOUR_USERNAME/techzone-frontend.git

# 6. Rename branch to main (if needed)
git branch -M main

# 7. Push to GitHub
git push -u origin main
```

**Enter your GitHub username and Personal Access Token when prompted.**

---

## 🔄 Update Code (After Changes)

```bash
# Check what files changed
git status

# See specific changes
git diff

# Add all changed files
git add .

# Or add specific file only
git add src/components/Header.tsx

# Commit with descriptive message
git commit -m "Updated product filtering logic"

# Push to GitHub
git push
```

---

## 📥 Clone Repository (On Another Computer)

```bash
# Clone from GitHub
git clone https://github.com/YOUR_USERNAME/techzone-frontend.git

# Navigate into folder
cd techzone-frontend

# Install dependencies
npm install

# Run development server
npm run dev

# Open browser to http://localhost:5173
```

---

## 🔍 Check Status Commands

```bash
# Check current status
git status

# View commit history
git log

# View commit history (compact)
git log --oneline

# Check remote URL
git remote -v

# Check current branch
git branch
```

---

## 🌿 Branch Commands (Optional)

```bash
# Create new branch
git branch feature/new-feature

# Switch to branch
git checkout feature/new-feature

# Create and switch in one command
git checkout -b feature/new-feature

# List all branches
git branch -a

# Merge branch into main
git checkout main
git merge feature/new-feature

# Delete branch
git branch -d feature/new-feature
```

---

## 🔄 Pull Latest Changes

```bash
# Pull latest from GitHub
git pull origin main

# Or just
git pull
```

---

## ❌ Undo Commands (Use Carefully!)

```bash
# Undo last commit (keep changes)
git reset --soft HEAD~1

# Undo last commit (discard changes)
git reset --hard HEAD~1

# Discard changes to specific file
git checkout -- src/App.tsx

# Discard ALL uncommitted changes
git reset --hard
```

---

## 🔧 Fix Common Issues

### **Issue: "Permission denied (publickey)"**
```bash
# Use HTTPS instead of SSH
git remote set-url origin https://github.com/YOUR_USERNAME/techzone-frontend.git
```

### **Issue: "Failed to push some refs"**
```bash
# Pull first, then push
git pull origin main --rebase
git push origin main
```

### **Issue: "Not a git repository"**
```bash
# Make sure you're in the right folder
pwd  # Shows current directory

# If wrong folder, navigate to correct one
cd /path/to/techzone-frontend

# Then initialize git
git init
```

### **Issue: Want to change commit message**
```bash
# Change last commit message
git commit --amend -m "New commit message"

# Push the change (force)
git push --force
```

### **Issue: Accidentally committed large file**
```bash
# Remove file from staging
git rm --cached path/to/large/file

# Add to .gitignore
echo "path/to/large/file" >> .gitignore

# Commit the removal
git commit -m "Remove large file"
git push
```

---

## 📝 Commit Message Best Practices

```bash
# Good commit messages:
git commit -m "Add product search functionality"
git commit -m "Fix cart total calculation bug"
git commit -m "Update API endpoint URL"
git commit -m "Refactor authentication logic"

# Bad commit messages (avoid):
git commit -m "fix"
git commit -m "update"
git commit -m "changes"
git commit -m "asdf"
```

---

## 🔐 GitHub Authentication

### **Personal Access Token (PAT)**

If GitHub asks for password:

1. Go to: https://github.com/settings/tokens
2. Click: "Generate new token" → "Generate new token (classic)"
3. Name: `techzone-frontend`
4. Expiration: Choose duration
5. Select scopes: ✅ `repo` (all)
6. Click: "Generate token"
7. **COPY TOKEN** (you won't see it again!)
8. Use this token as password when Git asks

### **Save Credentials (Optional)**

```bash
# Save username/token for 15 minutes
git config --global credential.helper cache

# Save username/token for 1 hour
git config --global credential.helper 'cache --timeout=3600'

# Save permanently (use carefully)
git config --global credential.helper store
```

---

## 🏷️ Tagging Releases

```bash
# Create a tag for version 1.0
git tag v1.0.0

# Push tag to GitHub
git push origin v1.0.0

# Create annotated tag (recommended)
git tag -a v1.0.0 -m "First stable release"
git push origin v1.0.0

# List all tags
git tag

# Delete tag locally
git tag -d v1.0.0

# Delete tag on GitHub
git push origin :refs/tags/v1.0.0
```

---

## 🔄 Sync Fork (If You Forked)

```bash
# Add upstream remote
git remote add upstream https://github.com/ORIGINAL_OWNER/techzone-frontend.git

# Fetch upstream changes
git fetch upstream

# Merge upstream into your main
git checkout main
git merge upstream/main

# Push to your fork
git push origin main
```

---

## 📦 npm Commands Reference

```bash
# Install all dependencies
npm install

# Install specific package
npm install react-router

# Install as dev dependency
npm install --save-dev typescript

# Uninstall package
npm uninstall package-name

# Update all packages
npm update

# Check for outdated packages
npm outdated

# Clean install (delete node_modules first)
rm -rf node_modules package-lock.json
npm install

# Run development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Run linter
npm run lint
```

---

## 🎯 Complete Workflow Example

```bash
# Day 1: Initial setup
cd techzone-frontend
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/YOU/techzone-frontend.git
git push -u origin main

# Day 2: Add feature
# ... make code changes ...
git add .
git commit -m "Add price filter to product search"
git push

# Day 3: Fix bug
# ... fix the bug ...
git add .
git commit -m "Fix cart total calculation when quantity is 0"
git push

# Day 4: On another computer
git clone https://github.com/YOU/techzone-frontend.git
cd techzone-frontend
npm install
npm run dev
# ... make changes ...
git add .
git commit -m "Update header logo"
git push

# Day 5: Back on first computer
git pull
npm run dev
```

---

## 🆘 Emergency: Start Over

```bash
# If everything is messed up and you want to start fresh:

# 1. Delete local Git history
rm -rf .git

# 2. Start over
git init
git add .
git commit -m "Fresh start"

# 3. Force push to GitHub (WARNING: This erases GitHub history)
git remote add origin https://github.com/YOU/techzone-frontend.git
git push -f origin main
```

**⚠️ Use with extreme caution! This erases all history.**

---

## 💡 Tips & Tricks

```bash
# Create aliases for common commands
git config --global alias.st status
git config --global alias.co checkout
git config --global alias.br branch
git config --global alias.ci commit

# Now you can use:
git st   # instead of git status
git co   # instead of git checkout
git br   # instead of git branch
git ci   # instead of git commit

# Set your name and email (required for commits)
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"

# Check your config
git config --list
```

---

## 📚 Quick Reference Summary

| Command | What It Does |
|---------|--------------|
| `git init` | Initialize new repository |
| `git add .` | Stage all changes |
| `git commit -m "msg"` | Commit with message |
| `git push` | Upload to GitHub |
| `git pull` | Download from GitHub |
| `git status` | Check what's changed |
| `git log` | View commit history |
| `git branch` | List/create branches |
| `git checkout` | Switch branches |
| `git clone` | Copy repository |

---

## 🔗 Useful Links

- Git Documentation: https://git-scm.com/doc
- GitHub Guides: https://guides.github.com
- Git Cheat Sheet: https://education.github.com/git-cheat-sheet-education.pdf
- Visual Git Reference: https://marklodato.github.io/visual-git-guide/index-en.html

---

**🎉 Save this file for quick reference when working with Git!**
