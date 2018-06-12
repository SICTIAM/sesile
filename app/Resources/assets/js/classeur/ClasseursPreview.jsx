import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'

import { Cell, GridX } from '../_components/UI'
import OnlyOffice from '../document/OnlyOffice'
import Helios from '../document/Helios'
import History from "../_utils/History"

class ClasseursPreview extends Component {
    static contextTypes = {
        t: func
    }
    state = {
        classeurs: []
    }
    componentDidMount() {
        this.setState({classeurs: this.props.location.state.classeurs, user: this.props.location.state.user})
    }
    componentDidUpdate() {
        $('.sign-role-list').foundation()
    }
    checkClasseur = (id, checked) => {
        const classeurs = this.state.classeurs
        classeurs[classeurs.findIndex(classeur => classeur.id === id)].checked = !checked
        this.setState({classeurs})
    }
    signClasseurs = (role = '') => {
        let idClasseursToSign = []
        this.state.classeurs.map(classeur => {
            if(classeur.checked) idClasseursToSign.push(classeur.id)
        })
        window.open(Routing.generate('jnlpSignerFiles', {id: encodeURIComponent(idClasseursToSign), role: role}))
        History.push('/classeurs/valides')
    }
    signClasseur = (id, role) => {
        window.open(Routing.generate('jnlpSignerFiles', {id, role}))
        History.push('/classeurs/valides')
    }
    render() {
        const { t } = this.context
        const { user } = this.state
        const documentsPreviewByClasseur =
            this.state.classeurs.map((classeur, key) =>
                <DocumentsPreviewByClasseur
                    signClasseur={this.signClasseur}
                    key={key}
                    classeur={classeur}
                    user={user} />)
        return (
            <GridX className="details-classeur">
                <Cell>
                    <GridX>
                        <Cell className=" medium-12 text-center">
                            <h1>{t('common.sign_classeur', {count: this.state.classeurs.length})}</h1>
                        </Cell>
                    </GridX>
                    {this.state.classeurs.length > 1 &&
                        <GridX>
                            <Cell>
                                <GridX className="grid-padding-y">
                                    <Cell>
                                        <GridX className="align-center-middle">
                                            <Cell className="medium-8">
                                                <h2>{t('common.sign_several_classeurs')}</h2>
                                            </Cell>
                                            <div className="cell medium-4 text-right sign-role-list">
                                                <button className="button hollow left-button-group arrow-only" data-toggle="button-classeurs-sign">
                                                    {t('common.sign_classeur_plural')} <i className="fa fa-caret-down"></i>
                                                </button>
                                                <div className="dropdown-pane" data-position="bottom" data-alignment="center" id="button-classeurs-sign" data-dropdown>
                                                    { (user && user.userrole && user.userrole.length > 0)
                                                        ? user.userrole.map(role => (
                                                            <li key={role.id} className="text-right">
                                                                <a onClick={() => this.signClasseurs(role.id)}
                                                                        title={role.user_roles}
                                                                        className="button secondary clear">
                                                                    {t('common.classeurs.button.role_as')} {role.user_roles}
                                                                </a>
                                                            </li>
                                                            )
                                                        )
                                                        : t('common.classeurs.button.no_roles')
                                                    }
                                                </div>
                                            </div>
                                        </GridX>
                                    </Cell>
                                </GridX>
                                <GridX>
                                    <Cell className="medium-12 panel">
                                        <ListClasseursToSign classeurs={this.state.classeurs} handleCheckClasseur={this.checkClasseur}/>
                                    </Cell>
                                </GridX>
                            </Cell>
                        </GridX>
                    }
                    <GridX>
                        <Cell>
                            <GridX className="grid-margin-x">
                                <Cell>
                                    <h2>{t('common.list_documents_by_classeur')}</h2>
                                </Cell>
                                <Cell>
                                    <GridX className="grid-margin-x">
                                        {documentsPreviewByClasseur}
                                    </GridX>
                                </Cell>
                            </GridX>
                        </Cell>
                    </GridX>
                </Cell>
            </GridX>
        )
    }
}

export default translate(['sesile'])(ClasseursPreview)

const ListClasseursToSign = ({classeurs, handleCheckClasseur}) => {
    let listClasseurs = classeurs.map((classeur, key) =>
        <li key={key}>
            <input
                type="checkbox"
                id={classeur.id}
                checked={classeur.checked}
                onChange={() => handleCheckClasseur(classeur.id, classeur.checked)}/>
            <a href={`#${classeur.nom}`}>
                <span className="text-bold">
                    {` ${classeur.nom}`}
                </span>
            </a>
            <br/>
        </li>)
    return (
        <GridX className="grid-padding-x grid-padding-y grid-margin-x">
            <Cell className="medium-auto">
                <ul className="no-bullet">
                    {listClasseurs.slice(0, 5)}
                </ul>
            </Cell>
            {listClasseurs.length > 5 &&
            <Cell className="medium-auto">
                <ul className="no-bullet">
                    {listClasseurs.slice(5, (listClasseurs.length <= 10 ? listClasseurs.length : 10))}
                </ul>
            </Cell>}
            {listClasseurs.length > 10 &&
            <Cell className="medium-auto">
                <ul className="no-bullet">
                    {listClasseurs.slice(10, listClasseurs.length)}
                </ul>
            </Cell>}
        </GridX>
    )
}

const DocumentsPreviewByClasseur = ({classeur, user, signClasseur}, {t}) => {
    const documentList = classeur.documents.map((document, key) =>
        <Preview key={key} document={document} user={user} />)
    return (
        <Cell>
            <GridX className="grid-padding-y">
                <Cell>
                    <div id={classeur.nom} className="grid-x align-center-middle">
                        <Cell className="medium-8">
                            <h3>{classeur.nom}</h3>
                        </Cell>
                        <div className="cell medium-4 text-right sign-role-list">
                            <button className="button hollow left-button-group arrow-only" data-toggle={`button-classeur-${classeur.id}-sign`}>
                                {t('common.sign_classeur')} <i className="fa fa-caret-down"/>
                            </button>
                            <div
                                className="dropdown-pane text-left"
                                data-position="bottom"
                                data-alignment="center"
                                id={`button-classeur-${classeur.id}-sign`}
                                data-dropdown
                                style={{padding: '5px'}}>
                                { (user && user.userrole && user.userrole.length > 0)
                                    ? user.userrole.map(role => (
                                            <li key={role.id} className="text-uppercase">
                                                <button
                                                    style={{padding: '5px'}}
                                                    onClick={() => signClasseur(classeur.id, role.id)}
                                                    title={role.user_roles}
                                                    className="button secondary clear">
                                                    {role.user_roles}
                                                </button>
                                            </li>
                                        )
                                    )
                                    : t('common.classeurs.button.no_roles')
                                }
                            </div>
                        </div>
                    </div>
                </Cell>
            </GridX>
            <GridX>
                <Cell className="medium-12 panel">
                    <GridX className="grid-margin-x grid-padding-x grid-padding-y">
                        {documentList}
                    </GridX>
                </Cell>
            </GridX>
        </Cell>
    )
}

DocumentsPreviewByClasseur.contextTypes = {
    t: func
}

const Preview = ({document, user}, {t}) => {
    const acceptedTypeMime = ['docx', 'doc', 'xlsx', 'xls', 'ppt', 'pptx', 'txt']
    return (
        <Cell>
            <GridX className="grid-margin-y">
                <Cell>
                    <h6 className="text-bold">
                        {document.name}
                    </h6>
                </Cell>
            </GridX>
            <GridX>
                {(acceptedTypeMime.includes(document.repourl.split('.').pop())) &&
                    <OnlyOffice document={document} user={user} revealDisplay={false} edit={false} />}
                {(document.type === 'application/pdf') &&
                    <iframe id={document.name} className="cell medium-12 only-office-height" src={`./../uploads/docs/${document.repourl}`}>
                        {t('common.browser_not_support_pdf')}, <a src={`./../uploads/docs/${document.repourl}`}>{t('common.download_pdf')}</a>
                    </iframe>}
                {document.type.includes('/xml') &&
                    <Helios document={document}/>}
                {(document.type.includes('image')) &&
                    <Cell>
                        <img src={`./../uploads/docs/${document.repourl}`} />
                    </Cell>}
            </GridX>
        </Cell>
    )
}

Preview.contextTypes = {
    t: func
}
