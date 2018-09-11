import React from 'react'
import History from '../_utils/History'

const AdminDetails = ({ className, title, subtitle, nom, children }) =>
    <div className={className}>
        <div id="admin-details" className="admin-details">
            <h2 className="text-center">{title}</h2>
            <p className="text-center">
                <em>{subtitle}</em>
            </p>
            <div className="admin-details-name">
                <span>{nom}</span>
            </div>
            {children}
        </div>
    </div>

const AdminDetailsWithInputField = ({ className, title, subtitle, nom, inputName, handleChangeName, placeholder, children }) =>
    <div className={className}>
        <div id="admin-details-input" className="admin-details">
            <h2 className="text-center">{title}</h2>
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
    <ul className="accordion" data-responsive-accordion-tabs="accordion large-accordion">
        {children}
    </ul>

const AccordionItem = ({ className, title, children, classNameChild }) =>
    <li className={"accordion-item " + className} data-accordion-item>
        <a className="accordion-title admin-details-accordion-title">{title}</a>
        <div className="accordion-content" data-tab-content >
            <div className={"grid-x grid-padding-x grid-padding-y " + classNameChild }>
                {children}
            </div>
        </div>
    </li>

const StepItem = ({ className, title, children, handleClickDeleteStep, stepKey }) =>
    <div className={className}>
        <div className="grid-x step-item">
            <div className="medium-12 cell name-step-item" style={{color: '#444'}}>
                {title}<a className="float-right" onClick={e => handleClickDeleteStep(stepKey)}><i className="fa fa-times-circle icon-size"></i></a>
            </div>
            <div className="medium-12 cell content-step-item">
                {children}
            </div>
        </div>
    </div>

const AdminPage = ({title, subtitle, className="", children}) =>
    <div id="admin-details" className={"admin-details " + className}>
        <h4 className="text-center text-bold text-uppercase">{title}</h4>
        <p className="text-center">
            <em>{subtitle}</em>
        </p>
        {children}
    </div>

const AdminList = ({title, headTitles, headGrid = [], labelButton, addLink, listLength= 0, emptyListMessage, children}) => {
    const listHeadTitles = headTitles.map((headTitle, index) =>
        <div
            key={headTitle}
            className={headGrid.length > 0 ? `cell ${headGrid[index]}` : 'cell medium-auto' }>
            {headTitle}
        </div>)

    const listHeadTitlesDoc = headTitles.map((headTitle, index) =>
        <div
            key={headTitle}
            className={headGrid.length > 0 ? `cell ${headGrid[index]}`
                : index === 0 ? 'cell large-6' : index === 1 ? 'cell medium-2'
                    : index === 2 ? 'cell small-1' : index == 3 ? 'cell medium-3' : 'cell medium-auto' }>
            {headTitle}
        </div>)

    const listHeadTitlesHelp = headTitles.map((headTitle, index) =>
        <div
            key={headTitle}
            className={headGrid.length > 0 ? `cell ${headGrid[index]}`
                : index === 0 ? 'cell large-6' : index === 1 ? 'cell medium-3'
                    : index === 2 ? 'cell small-1' : 'cell medium-auto' }>
            {headTitle}
        </div>)

    return(
        <div className="cell medium-10 list-admin">
            <div className="grid-x align-center-middle">
                <div className="cell medium-auto">
                    <h3>{title}</h3>
                </div>
                {(labelButton && addLink) &&
                <div className="cell medium-auto text-right">
                    <button className="button hollow" onClick={() => History.push(addLink)}>{labelButton}</button>
                </div>
                }
            </div>
            <div className="grid-x grid-padding-x panel">
                <div className="cell medium-12">
                    <div className="panel-heading grid-x grid-padding-x">
                        { title === "Les documents de mise à jour" ? (
                            listHeadTitlesDoc
                        ) : (
                            title === "Les documents d'aide") ? (
                            listHeadTitlesHelp
                        ) : (
                            listHeadTitles
                        )}
                    </div>
                    {
                        (listLength > 0) ? children :
                            <div className="grid-x grid-padding-x panel-body dashboard-title">
                                <div className="cell medium-12 text-center">
                                    {emptyListMessage}
                                </div>
                            </div>
                    }
                </div>
            </div>
        </div>
    )
}
const AdminContainer = ({children}) =>
    <div className="grid-x grid-margin-y align-center-middle">
        {children}
    </div>

const AdminListRow = ({children, link}) =>
    <div className="grid-x grid-padding-x panel-body dashboard-title">
        {children}
    </div>

export { AdminDetails, AdminDetailsWithInputField, SimpleContent, AccordionContent, AccordionItem, StepItem, AdminPage, AdminList, AdminContainer, AdminListRow }