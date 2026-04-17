import {
  getIconsCSS
} from '@iconify/utils'; // Utility for CSS generation
import bx from '@iconify/json/json/bx.json'; // Boxicons JSON
import bxl from '@iconify/json/json/bxl.json'; // Boxicons Light JSON
import bxs from '@iconify/json/json/bxs.json'; // Boxicons Solid JSON
import boxicons from '@iconify/json/json/boxicons.json'; // Boxicons generasi baru

// Combine all icon sets
const iconSets = [bx, bxl, bxs];

// Generate CSS content for the icons
function generateIconifyCSS() {
  const iconCssLama = iconSets.map(iconSet => {
    return getIconsCSS(iconSet, Object.keys(iconSet.icons), {
      iconSelector: '.{prefix}-{name}', // Example: .bx-cog
      commonSelector: '.bx', // Common selector for all icons, can be adjusted
      format: 'expanded' // Use 'compressed' for minified CSS
    });
  }).join('\n');

  const iconCssBaru = getIconsCSS(boxicons, Object.keys(boxicons.icons), {
    iconSelector: '.iconify-bx-{name}',
    commonSelector: '.iconify-bx',
    format: 'expanded'
  });

  const allIcons = [iconCssLama, iconCssBaru].join('\n');

  // Inject CSS directly into the page (instead of generating a file)
  const styleElement = document.createElement('style');
  styleElement.innerHTML = allIcons;
  document.head.appendChild(styleElement); // Append generated CSS to the document
}

// Call the function to generate and inject the CSS
generateIconifyCSS();
