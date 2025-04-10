import React from 'react';
import { Link } from 'react-router-dom';
import * as Icons from 'lucide-react';
import { iconButton } from '../../styles/tailwind-components';

/**
 * Reusable icon bar component that can be used throughout the application
 * for consistent icon display and interaction
 */
const IconBar = ({ 
  icons = [], 
  size = 20, 
  variant = 'primary',
  className = '',
  iconClassName = '',
  gap = 2
}) => {
  // Default styling based on variant
  const buttonStyle = iconButton[variant] || iconButton.primary;
  
  return (
    <div className={`flex items-center gap-${gap} ${className}`}>
      {icons.map((icon, index) => {
        const { name, onClick, tooltip, to, badge } = icon;
        const IconComponent = Icons[name] || Icons.HelpCircle;
        
        // Create the icon with proper styling
        const iconElement = (
          <div 
            key={index}
            className={`relative ${buttonStyle} ${iconClassName}`}
            onClick={onClick}
            title={tooltip}
          >
            <IconComponent size={size} />
            {badge && (
              <span className="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                {badge > 9 ? '9+' : badge}
              </span>
            )}
          </div>
        );
        
        // Wrap in Link if 'to' is provided
        if (to) {
          return (
            <Link key={index} to={to}>
              {iconElement}
            </Link>
          );
        }
        
        return iconElement;
      })}
    </div>
  );
};

export default IconBar;
