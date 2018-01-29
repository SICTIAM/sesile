import React from 'react'
import History from '../_utils/History'

const AdminDetails = ({ className, title, subtitle, nom, children }) => 
    <div className={className}>
        <div id="admin-details" className="admin-details">
            <h4 className="text-center text-bold text-uppercase">{title}</h4>
            <p className="text-center">{subtitle}</p>
            <div className="admin-details-name">
                <span>{nom}</span>
            </div>
            {children}
        </div>
    </div>

const AdminDetailsWithInputField = ({ className, title, subtitle, nom, inputName, handleChangeName, placeholder, children }) => 
    <div className={className}>
        <div id="admin-details-input" className="admin-details">
            <h4 className="text-center text-bold">{title}</h4>
            <p className="text-center">{subtitle}</p>
            <div className="admin-details-name">
                <input name={inputName} value={nom} onChange={(e) => handleChangeName(e.target.name, e.target.value)} placeholder={placeholder} autoFocus={true}/>
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

const AdminPage = ({title, subtitle, className="", children}) =>
    <div id="admin-details" className={"admin-details " + className}>
        <h4 className="text-center text-bold text-uppercase">{title}</h4>
        <p className="text-center">{subtitle}</p>
        {children}
    </div>

const AdminList = ({title, headTitles, labelButton, addLink, listLength= 0, emptyListMessage, children}) => {
    const listHeadTitles = headTitles.map(headTitle => <div key={headTitle} className="cell medium-auto">{headTitle}</div>)
    return(
        <div className="cell medium-10 list-admin">
            <div className="grid-x align-center-middle">
                <div className="cell medium-auto">
                    <h3>{title}</h3>
                </div>
                {(labelButton && addLink) &&
                    <div className="cell medium-auto text-right">
                        <button className="button" onClick={() => History.push(addLink)}>{labelButton}</button>
                    </div>
                }
            </div>
            <div className="grid-x grid-padding-x panel">
                <div className="cell medium-12 panel-heading grid-x">
                    {listHeadTitles}
                </div>
                {
                    (listLength > 0) ? children :
                    <div className="cell medium-12 panel-body">
                        <div className="text-center">
                            {emptyListMessage}
                        </div>
                    </div>
                }
            </div>
        </div>
    )
}

const AdminContainer = ({children}) => 
    <div className="grid-x grid-margin-y align-center-middle">
        {children}
    </div>

const AdminListRow = ({children, link}) =>
    <div className="cell medium-12 panel-body grid-x align-center-middle">
        {children}
    </div>

export { AdminDetails, AdminDetailsWithInputField, SimpleContent, AccordionContent, AccordionItem, StepItem, AdminPage, AdminList, AdminContainer, AdminListRow }