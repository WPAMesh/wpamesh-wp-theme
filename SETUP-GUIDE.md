# WPAMesh Theme Setup Guide

This guide will walk you through setting up and customizing the WPAMesh WordPress theme.

---

## Table of Contents

1. [Installing the Theme](#1-installing-the-theme)
2. [Understanding the Site Editor](#2-understanding-the-site-editor)
3. [Editing Navigation](#3-editing-navigation)
4. [Customizing the Homepage](#4-customizing-the-homepage)
5. [Editing Sidebar Widgets](#5-editing-sidebar-widgets)
6. [Uploading Your Logo](#6-uploading-your-logo)
7. [Updating Network Stats](#7-updating-network-stats)
8. [Editing the Footer](#8-editing-the-footer)
9. [Common Tasks](#9-common-tasks)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. Installing the Theme

### Step 1: Upload the Theme
1. Log into your WordPress admin dashboard
2. Go to **Appearance > Themes**
3. Click **Add New** at the top
4. Click **Upload Theme**
5. Choose the `wpamesh-theme.zip` file
6. Click **Install Now**

### Step 2: Activate the Theme
1. After installation completes, click **Activate**
2. Your site will now use the WPAMesh theme

> **Important:** After activating, you may need to save permalinks. Go to **Settings > Permalinks** and click **Save Changes** (even without making changes).

---

## 2. Understanding the Site Editor

The WPAMesh theme uses WordPress **Full Site Editing (FSE)**. This means you can visually edit your entire site layout.

### Opening the Site Editor
1. Go to **Appearance > Editor** (you'll see "Editor" not "Customize")
2. You'll see a preview of your site

### Key Concepts

**What is a Pattern?**
A pattern is a pre-designed group of blocks you can edit. Think of it like a template section. Click on any pattern to change its content - just like editing a document.

**What is a Template Part?**
Template parts are reusable sections like the header, footer, and sidebars. Edit once, and changes appear everywhere that part is used.

### Navigation Tips
- Click the **WordPress icon** (top-left) to access navigation
- **Templates** - Full page layouts (homepage, single post, etc.)
- **Template Parts** - Reusable sections (header, footer, sidebars)
- **Styles** - Global colors, fonts, spacing

### Saving Your Changes
- Click the blue **Save** button in the top-right corner
- **Changes are NOT live until you save!**
- You can preview changes before saving

### Undo Mistakes
- Press **Ctrl+Z** (Windows) or **Cmd+Z** (Mac) to undo
- To reset a pattern: delete it and re-insert from the Patterns panel

---

## 3. Editing Navigation

The left sidebar contains four navigation sections. Here's how to edit them:

### Step 1: Open the Sidebar Template Part
1. Go to **Appearance > Editor**
2. Click the **WordPress icon** (top-left)
3. Click **Patterns** or **Template Parts**
4. Find and click **Left Sidebar**

### Step 2: Edit Navigation Links
1. Click on any navigation link in the preview
2. A toolbar appears - click the link icon to change the URL
3. Or click the text to rename the link
4. To add a link: Click the "+" at the end of a navigation section
5. To remove a link: Click the link, then click the three dots (...) > Remove

### Default Navigation Structure

**Getting Started:**
| Label | URL |
|-------|-----|
| What's Meshtastic? | /whats-meshtastic/ |
| Joining the WPAMesh | /posts/getting-on-the-air-with-the-wpamesh/ |
| Flash Your Device | https://flasher.meshtastic.org/ |
| Video Tutorial | https://www.youtube.com/watch?v=Euw9ppwkbxQ |

**View The Mesh:**
| Label | URL |
|-------|-----|
| Live Map | https://map.wpamesh.net/map |
| Member Nodes | /member-nodes/ |
| MQTT Server | /mqtt/ |

**Guides:**
| Label | URL |
|-------|-----|
| RoadsEnd Tower | /posts/roadsend-tower/ |
| All Guides | /posts/category/guide/ |

**Community:**
| Label | URL |
|-------|-----|
| Discord | https://discord.gg/67xqYqaqXP |
| Facebook | https://www.facebook.com/groups/873345134557415 |
| Meshtastic.org | https://meshtastic.org/ |

---

## 4. Customizing the Homepage

### Editing the Hero Section
1. Go to **Appearance > Editor**
2. The homepage should load by default (or click Templates > Front Page)
3. Click on the hero banner section
4. To change the title: Click the "Meshtastic For The Yinzers" text and type
5. To change the subtitle: Click the description text
6. To change the button: Click the button text or link

### Changing the Hero Image
1. Click on the hero banner background image
2. Click **Replace** in the toolbar
3. Upload a new image or choose from media library
4. Recommended: Use a wide panoramic image (at least 1200px wide)

### Editing the Welcome Text
1. Scroll down to the "Welcome" section
2. Click on any paragraph to edit it
3. Type your new content directly

### Changing the Map Image
1. Find the "Coverage Map" section
2. Click on the map image
3. Click **Replace** to upload a new screenshot
4. Update the link if needed (click the image, then the link icon)

---

## 5. Editing Sidebar Widgets

### Right Sidebar (Wide Screens Only)
The right sidebar appears on screens wider than 1400px. It contains:

- **Network Stats** - Node counts and statistics
- **Upcoming Events** - Event listings
- **Discord Widget** - Join button
- **Featured Node** - Highlighted infrastructure

### To Edit Right Sidebar:
1. Go to **Appearance > Editor**
2. Click **WordPress icon > Template Parts > Right Sidebar**
3. Click on any section to edit

### Editing Events
1. Find the "Upcoming Events" section
2. Click on the date, title, or location to change them
3. To add an event: Click below an existing event and insert a new Group block, then copy the structure

### Editing Discord Widget
1. Find "Join the Community" section
2. Change the "23 members online" text to your current count
3. The Discord link is pre-configured to your server

---

## 6. Uploading Your Logo

### Option 1: Site Editor (Recommended)
1. Go to **Appearance > Editor**
2. Click on the logo area in the sidebar (📡 icon area)
3. Click **Replace** or select the Site Logo block
4. Upload your logo image

### Option 2: Site Settings
1. Go to **Settings > General**
2. Look for Site Icon option
3. Upload a square image (512x512px recommended)

### Logo Recommendations
- **Size:** 36x36px for sidebar, or 150x50px for wider logos
- **Format:** PNG with transparent background, or SVG
- **SVG tip:** SVG is a vector format that stays crisp at any size

---

## 7. Updating Network Stats

The Network Stats widget shows:
- Total Nodes
- Online Nodes
- Counties Covered
- Repeaters

### To Update Stats:
1. Go to **Appearance > Editor**
2. Navigate to **Template Parts > Right Sidebar**
3. Find the "Network Stats" section
4. Click directly on any number to type a new value
5. Click **Save**

> **Note:** These values are manual. Update them periodically when your network changes.

### Updating the Left Sidebar Status Widget
1. Navigate to **Template Parts > Left Sidebar**
2. Scroll to the bottom "Network Status" section
3. Click on values like "Long_Fast", "Moderate", "Elevated", "Active"
4. Type new values as needed
5. For status colors: The CSS classes `good` and `warn` control colors
   - `good` = green with bullet
   - `warn` = orange/rust with triangle

---

## 8. Editing the Footer

1. Go to **Appearance > Editor**
2. Click **WordPress icon > Template Parts > Footer**
3. Click on the copyright text to edit
4. Update the year or add additional links

---

## 9. Common Tasks

### Adding a New Page
1. Go to **Pages > Add New**
2. Write your content using the block editor
3. Click **Publish**

### Adding a New Post
1. Go to **Posts > Add New**
2. Write your content
3. Assign a category (Node Detail, Guide, etc.)
4. Click **Publish**
5. The post will automatically appear in Recent Posts

### Changing Colors Globally
1. Go to **Appearance > Editor**
2. Click the **Styles** icon (paintbrush, top-right)
3. Click **Colors**
4. Adjust the color palette
5. Save changes

> **Note:** The theme uses Pittsburgh-inspired colors. Gold (#FFB81C) is the primary accent.

### Changing Fonts
1. Go to **Appearance > Editor > Styles**
2. Click **Typography**
3. Choose from available font families
4. The theme includes Barlow and Barlow Condensed

---

## 10. Troubleshooting

### Theme looks broken after activation
- Go to **Settings > Permalinks** and click **Save Changes**
- Clear any caching plugins

### Navigation links not working
- Check that the URLs start with `/` for internal links
- External links should include `https://`

### Sidebar not showing on mobile
- This is by design! The sidebar becomes a slide-out menu on mobile
- Click the hamburger menu (☰) to access navigation

### Right sidebar not appearing
- The right sidebar only shows on screens wider than 1400px
- This is intentional to prevent crowding on smaller screens

### Changes not appearing on site
- Make sure you clicked the blue **Save** button
- Clear your browser cache (Ctrl+Shift+R)
- If using a caching plugin, purge the cache

### Want to reset to default?
- Go to **Appearance > Editor**
- Click **WordPress icon > Templates** (or Template Parts)
- Click the three dots (...) next to the item
- Select **Clear customizations** to reset to default

---

## Need Help?

- **Discord:** https://discord.gg/67xqYqaqXP
- **Facebook:** https://www.facebook.com/groups/873345134557415

The WPAMesh community is happy to help with any setup questions!
