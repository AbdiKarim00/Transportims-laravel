# Transport IMS Brand Guidelines

## Color Palette

### Primary Colors

- **Primary Gold**
  - HSL: 44 58% 48%
  - Hex: #C5A830
  - Usage: Primary actions, buttons, and key UI elements
  - Gradient: linear-gradient(135deg, #D9BE50, #C5A830)

### Secondary Colors

- **Emerald Green**
  - HSL: 164 67% 13%
  - Hex: #0B5D4C
  - Usage: Secondary actions, accents, and supporting elements

### Background Colors

- **Light Background**
  - HSL: 164 40% 95%
  - Hex: #E6F4EF
  - Usage: Main background, cards, and containers

- **Dark Background**
  - HSL: 222.2 84% 4.9%
  - Hex: #0A0F1C
  - Usage: Dark mode backgrounds

### Accent Colors

- **Muted**
  - HSL: 164 40% 95%
  - Usage: Subtle backgrounds, disabled states

- **Destructive**
  - HSL: 0 84.2% 60.2%
  - Usage: Error states, destructive actions

## Typography

### Font Family

- Primary: Figtree (400, 500, 600)
- Fallback: System UI

### Text Colors

- **Foreground**
  - Light: HSL 222.2 84% 4.9%
  - Dark: HSL 210 40% 98%

- **Muted**
  - Light: HSL 215.4 16.3% 46.9%
  - Dark: HSL 215 20.2% 65.1%

## Components

### Buttons

#### Primary Button (Gold)

```html
<button class="gold-button px-4 py-2 rounded-lg text-white">
  Primary Action
</button>
```

- Gradient background
- Subtle shadow
- Hover animation
- Active state feedback

#### Secondary Button

```html
<button class="bg-secondary text-secondary-foreground px-4 py-2 rounded-lg">
  Secondary Action
</button>
```

### Cards

#### Standard Card

```html
<div class="bg-card text-card-foreground rounded-lg shadow-md p-6">
  Card Content
</div>
```

#### Login Container

```html
<div class="login-container rounded-lg p-8">
  Login Form Content
</div>
```

- White background
- Subtle shadow
- Hover animation
- Border with low opacity

### Form Elements

#### Input Fields

```html
<input class="login-input w-full px-4 py-2 rounded-lg border border-input focus:ring-2 focus:ring-primary">
```

- Smooth transition
- Focus state with gold ring
- Consistent border radius

### Animations

#### Fade In

```css
.animate-fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}
```

#### Welcome Box Animation

```css
.welcome-box {
  position: relative;
  overflow: hidden;
}
```

- Radial gradient animation
- 15-second rotation cycle

## Layout

### Spacing

- Base unit: 0.5rem (8px)
- Container padding: 2rem (32px)
- Component spacing: 1rem (16px)

### Border Radius

- Default: 0.5rem (8px)
- Buttons: 0.5rem (8px)
- Cards: 0.5rem (8px)

## Dark Mode

### Color Adjustments

- Inverted background colors
- Adjusted contrast ratios
- Preserved primary gold
- Modified secondary colors

### Component Adjustments

- Adjusted shadows
- Modified gradients
- Preserved animations

## Usage Examples

### Login Page

```html
<div class="emerald-gradient min-h-screen flex items-center justify-center p-4">
  <div class="login-container w-full max-w-md">
    <h1 class="text-2xl font-semibold text-shadow-sm">Welcome Back</h1>
    <!-- Form content -->
  </div>
</div>
```

### Dashboard Card

```html
<div class="bg-card rounded-lg shadow-md p-6 animate-fade-in">
  <h2 class="text-xl font-semibold text-card-foreground">Dashboard Title</h2>
  <p class="text-muted-foreground mt-2">Card content</p>
</div>
```

## Best Practices

1. **Color Usage**
   - Use primary gold sparingly for emphasis
   - Maintain consistent color hierarchy
   - Ensure sufficient contrast ratios

2. **Typography**
   - Maintain consistent font weights
   - Use appropriate text colors for context
   - Follow established spacing patterns

3. **Components**
   - Use provided component classes
   - Maintain consistent spacing
   - Follow animation guidelines

4. **Responsive Design**
   - Use appropriate breakpoints
   - Maintain readability at all sizes
   - Preserve component integrity

5. **Accessibility**
   - Ensure sufficient color contrast
   - Provide appropriate focus states
   - Support keyboard navigation

## Implementation Notes

1. **CSS Variables**
   - Use provided CSS variables for consistency
   - Maintain dark mode compatibility
   - Follow established naming conventions

2. **Animations**
   - Keep animations subtle and purposeful
   - Maintain performance considerations
   - Use provided animation classes

3. **Responsive Design**
   - Use Tailwind's responsive prefixes
   - Maintain mobile-first approach
   - Test across all breakpoints
