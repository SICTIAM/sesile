import React from 'react'

const AdminDetails = ({ className, title, subtitle, nom, children }) => 
    <div className={className}>
        <div id="admin-details" className="admin-details">
            <h4 className="text-center text-bold">{title}</h4>
            <p className="text-center">{subtitle}</p>
            <div className="admin-details-name">{nom}</div>
            {children}
        </div>
    </div>

const AccordionContent = ({children}) =>
    <ul className="accordion" data-responsive-accordion-tabs="accordion medium-tabs large-accordion">
        {children}
    </ul>

const AccordionItem = ({ className, title, children }) =>
    <li className={"accordion-item " + className} data-accordion-item>
        <a className="accordion-title admin-details-accordion-title">{title}</a>
        <div className="accordion-content" data-tab-content >
            <div className="grid-x grid-padding-x grid-padding-y">
                {children}
            </div>
        </div>
    </li>

export { AdminDetails, AccordionContent, AccordionItem }