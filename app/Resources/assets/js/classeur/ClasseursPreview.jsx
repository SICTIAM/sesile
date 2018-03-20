import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'

import { Cell, GridX } from '../_components/UI'
import { Button } from '../_components/Form'
import OnlyOffice from '../document/OnlyOffice'
import Helios from '../document/Helios'

class ClasseursPreview extends Component {
    static contextTypes = {
        t: func
    }
    state = {
        classeurs: []
    }
    componentDidMount() {
        this.setState({classeurs: this.props.location.state.classeurs})
    }
    checkClasseur = (id, checked) => {
        const classeurs = this.state.classeurs
        classeurs[classeurs.findIndex(classeur => classeur.id === id)].checked = !checked
        this.setState({classeurs})
    }
    signClasseurs = () => {
        let idClasseursToSign = []
        this.state.classeurs.map(classeur => {
            if(classeur.checked) idClasseursToSign.push(classeur.id)
        })
        window.open(Routing.generate('jnlpSignerFiles', {id: encodeURIComponent(idClasseursToSign)}))
    }
    render() {
        const { t } = this.context
        const documentsPreviewByClasseur =
            this.state.classeurs.map((classeur, key) =>
                <DocumentsPreviewByClasseur
                    key={key}
                    classeur={classeur}
                    user={this.props.location.state.user} />)
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
                                            <Cell className="medium-10">
                                                <h2>{t('common.sign_several_classeurs')}</h2>
                                            </Cell>
                                            <Button
                                                className="cell medium-2 text-right"
                                                onClick={this.signClasseurs}
                                                labelText={t('common.sign_classeur_plural')}/>
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

const DocumentsPreviewByClasseur = ({classeur, user}, {t}) => {
    const documentList = classeur.documents.map((document, key) =>
        <Preview key={key} document={document} user={user} />)
    return (
        <Cell>
            <GridX className="grid-padding-y">
                <Cell>
                    <div id={classeur.nom} className="grid-x align-center-middle">
                        <Cell className="medium-10">
                            <h3>{classeur.nom}</h3>
                        </Cell>
                        <Cell className="medium-2 text-right">
                            <a
                                className="button hollow"
                                href={Routing.generate('jnlpSignerFiles', {id: classeur.id})}>
                                {t('common.sign_classeur')}
                            </a>
                        </Cell>
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
