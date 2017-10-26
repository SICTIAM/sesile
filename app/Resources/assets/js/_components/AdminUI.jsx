import React from 'react'

const AdminDetails = ({ className, title, subtitle, nom, children }) => 
    <div className={className}>
        <div id="admin-details" className="admin-details">
            <h4 className="text-center text-bold">{title}</h4>
            <p className="text-center">{subtitle}</p>
            <div className="admin-details-name">
                <span>{nom}</span>
            </div>
            {children}
        </div>
    </div>

const AdminDetailsInput = ({ className, title, subtitle, nom, inputName, handleChangeName, placeholder, children }) => 
    <div className={className}>
        <div id="admin-details-input" className="admin-details">
            <h4 className="text-center text-bold">{title}</h4>
            <p className="text-center">{subtitle}</p>
            <div className="admin-details-name">
                <input name={inputName} value={nom} onChange={(e) => handleChangeName(e.target.name, e.target.value)} placeholder={placeholder} />
                <i className={"fi-pencil small"}></i>
            </div>
            {children}
        </div>
    </div>

const SimpleContent = ({className, children}) => 
    <div className={"admin-content-details " + className }>
        {children}
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

const StepItem = ({ className, title, children, handleClickDeleteStep, stepKey }) => 
    <div className={className}>
        <div className="grid-x step-item">
            <div className="medium-12 cell name-step-item">
            {title}<a className="float-right" style={{color: "red"}} onClick={e => handleClickDeleteStep(stepKey)}>x</a>
            </div>
            <div className="medium-12 cell content-step-item">
                {children}
            </div>
        </div>
    </div>

export { AdminDetails, AdminDetailsInput, SimpleContent, AccordionContent, AccordionItem, StepItem }