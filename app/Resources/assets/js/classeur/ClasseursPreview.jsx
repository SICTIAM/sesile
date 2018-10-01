import React, { Component } from 'react'
import {func, object} from 'prop-types'
import { translate } from 'react-i18next'

import { Cell, GridX } from '../_components/UI'
import OnlyOffice from '../document/OnlyOffice'
import Helios from '../document/Helios'
import History from "../_utils/History"

class ClasseursPreview extends Component {
    static contextTypes = {
        t: func,
        user: object
    }
    state = {
        classeurs: []
    }
    componentDidMount() {
        if(this.state.classeurs.length < 1) {
            this.setState({classeurs: this.props.location.state.classeurs})
        }
        $('#sign-classeurs').foundation()
    }
    componentWillUnmount() {
        clearInterval(this.interval)
    }
    checkClasseur = (id, checked) => {
        const classeurs = this.state.classeurs
        const classeur = classeurs[classeurs.findIndex(classeur => classeur.id === id)]
        if(classeur.status === 1) {
            classeur.checked = !checked
            this.setState({classeurs})
        }
    }
    signClasseurs = (role = '') => {
        let idClasseursToSign = []
        this.state.classeurs.map(classeur => {
            if(classeur.checked) idClasseursToSign.push(classeur.id)
        })
        window.open(Routing.generate('jnlpSignerFiles', {id: encodeURIComponent(idClasseursToSign), role: role}))
        this.interval = setInterval(() => {
            this.getClasseurStatus(idClasseursToSign)
        }, 10000)
    }
    signClasseur = (id, role) => {
        window.open(Routing.generate('jnlpSignerFiles', {id, role}))
        this.interval = setInterval(() => {
            this.getClasseurStatus(id)
        }, 10000)
    }
    getClasseurStatus = (id) => {
        fetch(
            Routing.generate(
                'sesile_classeur_classeurapi_statusclasseur',
                {orgId: this.context.user.current_org_id, id: encodeURIComponent(id)}),
            {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => {
                const classeurs = this.state.classeurs
                let allSigned = json.map((classeurStatus) => {
                    const indexOfClasseur = classeurs.findIndex(classeur => classeur.id === classeurStatus.id)
                    if(classeurs[indexOfClasseur].status !== classeurStatus.status) {
                        classeurs[indexOfClasseur].status = classeurStatus.status
                        classeurs[indexOfClasseur].checked = false
                    }
                    return classeurStatus.status === 2
                })
                if(allSigned.every((value) => value === true)) {
                    clearInterval(this.interval)
                    setTimeout(() => History.push('/classeurs/valides'), 5000)
                }
                this.setState({classeurs})
            })
    }
    openMultiSignatureDropdown = (e) => {
        let dropdown = document.getElementById("button-classeurs-sign")
        dropdown.classList.contains('is-open') ? dropdown.classList.remove("is-open") : dropdown.classList.add("is-open")
    }
    render() {
        const { t, user } = this.context
        const documentsPreviewByClasseur =
            this.state.classeurs.map((classeur, key) =>
                <DocumentsPreviewByClasseur
                    key={`classeur-${classeur.id}-documents`}
                    signClasseur={this.signClasseur}
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
                        <div className="grid-x" style={{visibility: `${this.state.classeurs.length > 1 ? 'visible' : 'hidden'}`}}>
                            <Cell>
                                <GridX className="grid-padding-y">
                                    <Cell>
                                        <GridX className="align-center-middle">
                                            <Cell className="medium-8">
                                                <h2>{t('common.sign_several_classeurs')}</h2>
                                            </Cell>
                                            <div id="sign-classeurs" className="cell medium-4 text-right">
                                                <button className="button hollow left-button-group arrow-only" data-toggle="button-classeurs-sign">
                                                    {t('common.sign_classeur_plural')} <i className="fa fa-caret-down"/>
                                                </button>
                                                <div
                                                    className="dropdown-pane"
                                                    data-position="bottom"
                                                    data-alignment="right"
                                                    id="button-classeurs-sign"
                                                    style={{padding: '0'}}
                                                    data-close-on-click={true}
                                                    data-dropdown data-auto-focus={true}>
                                                    <ul className="no-bullet">
                                                        {(user && user.userrole && user.userrole.length > 0) ?
                                                            user.userrole.map(role =>
                                                                <li key={role.id} className="text-right no-bullet">
                                                                    <a onClick={() => this.signClasseurs(role.id)}
                                                                       title={role.user_roles}
                                                                       className="button secondary clear">
                                                                        {role.user_roles}
                                                                    </a>
                                                                </li>)
                                                            : t('common.classeurs.button.no_roles')}
                                                    </ul>
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
                        </div>
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
        <li key={`classeur-${classeur.id}`}>
            <div className="pretty p-default p-curve p-thick" style={{marginRight: '5px'}}>
                <input
                    type="checkbox"
                    id={classeur.id}
                    checked={classeur.checked}
                    disabled={classeur.status === 2}
                    onChange={() => handleCheckClasseur(classeur.id, classeur.checked)}/>
                <div className="state p-primary-o">
                    <label/>
                </div>
            </div>
            <a href={`#${classeur.nom}`} className="text-bold" style={{textDecoration:"underline", marginRight: '5px'}}>
                {classeur.nom}
            </a>
            {classeur.status === 2 &&
                <i style={{color: '#39922c'}} >signé</i>}
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

class DocumentsPreviewByClasseur extends Component  {
    componentDidMount() {
        $(`#sign-classeur-${this.props.classeur.id}`).foundation()
    }
    componentWillUnmount() {
        $(`#sign-classeur-${this.props.classeur.id}`) > 0 && $(`#sign-classeur-${this.props.classeur.id}`).foundation('_destroy')
    }
    render() {
        const { t } = this.context
        const documentList = this.props.classeur.documents.map((document, key) =>
            <Preview key={`document-${document.id}`} document={document} user={this.props.user} />)
        return (
            <Cell>
                <GridX className="grid-padding-y">
                    <Cell>
                        <div id={this.props.classeur.nom} className="grid-x align-center-middle">
                            <Cell className="medium-auto">
                                <h3>{this.props.classeur.nom}</h3>
                            </Cell>
                            {this.props.classeur.status === 1 &&
                                <div id={`sign-classeur-${this.props.classeur.id}`} className={`cell medium-auto text-right`}>
                                    <button
                                        className="button hollow left-button-group arrow-only"
                                        data-toggle={`button-classeur-${this.props.classeur.id}-sign`}>
                                        {t('common.sign_classeur')} <i className="fa fa-caret-down"/>
                                    </button>
                                    <div
                                        className="dropdown-pane text-left"
                                        data-position="bottom"
                                        data-alignment="center"
                                        id={`button-classeur-${this.props.classeur.id}-sign`}
                                        data-close-on-click={true}
                                        data-dropdown data-auto-focus={true}
                                        style={{padding: '5px'}}>
                                        <ul className=" no-bullet">
                                            {(this.props.user && this.props.user.userrole && this.props.user.userrole.length > 0) ?
                                                this.props.user.userrole.map(role =>
                                                    <li key={role.id}>
                                                        <span
                                                            title={role.user_roles}
                                                            onClick={() => this.props.signClasseur(this.props.classeur.id, role.id)}
                                                            className="button secondary clear">
                                                            {role.user_roles}
                                                        </span>
                                                    </li>)
                                                : t('common.classeurs.button.no_roles')}
                                        </ul>
                                    </div>
                                </div>}
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
