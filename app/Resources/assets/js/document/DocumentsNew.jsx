import React, { Component } from 'react'
import { translate } from 'react-i18next'
import Dropzone from 'react-dropzone'
import {array, func, object, bool} from 'prop-types'

import {Cell, GridX} from '../_components/UI'
import { BytesToSize, handleErrors } from '../_utils/Utils'
import DowSign from "./DowSign"


class DocumentsNew extends Component {

    static contextTypes = {
        t: func
    }
    state = {
        disabled: true,
        accept: '',
        dropFileError: '',
        multiple: false,
        fileRule: '',
        collectivite: {}
    }

    componentDidMount() {
        this.fetchCollectivite()
    }

    fetchCollectivite() {
        fetch(Routing.generate('sesile_main_collectiviteapi_getbyid', {id: this.props.user.current_org_id}),
            {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(collectivite => this.setState({collectivite}))
            .catch(() => this.setState({collectivite: {ordonnees_signature: 10, abscisses_signature: 10}}))
    }

    componentWillReceiveProps(nextProps) {
        const {t} = this.context
        const {typeClasseur, documents, editClasseur} = nextProps

        if (typeClasseur.nom === "Helios" && documents.length > 0 || !editClasseur) {
            this.setState({disabled: true})
        }
        else if (!editClasseur) {
            this.setState({disabled: true})
        }
        else {
            this.setState({disabled: false})
        }

        if (typeClasseur && typeClasseur.nom === "Helios") {
            this.setState({accept: 'text/xml', multiple: false})
            this.setState({fileRule: t('common.documents.error.file_acceptation_rules_helios')})
        } else {
            this.setState({accept: 'image/*, application/*, text/*', multiple: true})
            this.setState({fileRule: t('common.documents.error.file_acceptation_rules')})
        }
    }

    handleChangeCollectiviteValue = (name, value) => {
        const {collectivite} = this.state
        collectivite[name] = value
        this.setState({collectivite})
    }
    handleChangeSignaturePosition = (position) => {
        this.handleChangeCollectiviteValue("abscisses_signature", position.x)
        this.handleChangeCollectiviteValue("ordonnees_signature", position.y)
    }
    handleChangeVisaPosition = (position) => {
        this.handleChangeCollectiviteValue("abscisses_visa", position.x)
        this.handleChangeCollectiviteValue("ordonnees_visa", position.y)
    }
    isHeliosAndExistingClasseur = () => {
        return this.props.classeurId && this.props.typeClasseur.nom === "Helios"
    }
    isHeliosAndHaveOneDocument = () => {
        return this.props.typeClasseur.nom === "Helios" && this.props.documents.length === 1
    }
    isNewDocument = (document) => !document.id
    isPdfAndExistingDocument = (document) => {
        return document.id && document.type === "application/pdf"
    }
    classeurIsFinalized = () => this.props.statusClasseur === 2
    isFinalizedOrRetiredClasseur = () => this.classeurIsFinalized() || this.props.statusClasseur === 3
    userNotHaveSignatureImage = () => this.props.user.path_signature && this.props.user.path_signature.trim() !== ""
    isPendingAndHeliosTypeCLasseur = () => this.props.statusClasseur === 1 && this.props.typeClasseur.nom === 'Helios'

    render() {
        const {t} = this.context
        const {onDrop, removeDocument, onClick, displayReveal, user} = this.props
        const {disabled, accept, multiple, fileRule} = this.state
        const docs = this.props.documents.map((document, key) =>
            <div
                key={key}
                className=""
                style={{
                    width: '290px',
                    display: 'inline-block',
                    marginRight: '1em',
                    marginBottom: '1em',
                    boxShadow: '0 1px 2px 0 rgba(34, 36, 38, 0.15)',
                    borderRadius: '0.28571429rem',
                    border: '1px solid rgba(34, 36, 38, 0.15'
                }}>
                <div
                    onClick={(e) => onClick(e, document.id)}
                    className="grid-x doc-action-button"
                    title={document.name}
                    style={{padding: '0.5em'}}>
                    <div className="cell medium-2 align-middle" style={{display: 'flex'}}>
                        <i className="fa fa-file-o" style={{fontSize: '2em'}}/>
                    </div>
                    <div className="cell medium-auto">
                        <GridX>
                            <Cell className="text-truncate">
                                <span className="text-bold" style={{fontSize: '0.9em'}}>{document.name}</span>
                            </Cell>
                        </GridX>
                        <GridX>
                            <Cell>
                                <span style={{fontSize: '1em', color: 'rgba(0,0,0,0.4)'}}>
                                    {`${BytesToSize(document.size)} - ${document.name.split('.').pop()}`}
                                </span>
                            </Cell>
                        </GridX>
                    </div>
                </div>
                <hr style={{margin: 0}}/>
                <div className="grid-x">
                    <div
                        title={t('common.expand_preview')}
                        onClick={(e) => {
                            if (!this.isNewDocument(document)) displayReveal(e, document.id)
                        }}
                        className={
                            `cell medium-auto align-center
                            doc-action-button
                            ${this.isNewDocument(document) && ' disabled'}`}
                        style={{display: 'flex'}}>
                        <i className="fa fa-expand" style={{padding: '5px'}}/>
                    </div>
                    {this.isPdfAndExistingDocument(document) && this.classeurIsFinalized(document) ?
                        <DowSign
                            document={document}
                            collectivite={this.state.collectivite}
                            user={user}/> :
                        this.isNewDocument(document) ?
                            <div
                                title={t('common.download')}
                                className={
                                    `cell medium-auto align-center
                                    doc-action-button
                                    disabled`}
                                style={{display: 'flex', borderLeft: 'solid 1px #b3b2b2'}}>
                                <i className="fa fa-download" style={{padding: '5px'}}/>
                            </div> :
                            <a
                                title={t('common.download')}
                                href={Routing.generate('download_doc', {id: document.id})}
                                className={
                                    `cell medium-auto align-center
                                    doc-action-button
                                    ${this.isNewDocument(document) && ' disabled'}`}
                                style={{display: 'flex', borderLeft: 'solid 1px #b3b2b2'}}>
                                <i className="fa fa-download" style={{padding: '5px'}}/>
                            </a>}
                    <div
                        title={t('common.button.delete')}
                        className={
                            `cell medium-auto align-center doc-action-button
                             ${(this.isHeliosAndExistingClasseur() || this.isFinalizedOrRetiredClasseur() || !this.props.editClasseur) &&
                            ' disabled'}`}
                        style={{
                            display: 'flex',
                            borderLeft: 'solid 1px #b3b2b2',
                            cursor: `${(!this.isHeliosAndExistingClasseur() || !this.isFinalizedOrRetiredClasseur() || this.props.editClasseur) && ' disabled'}`
                        }}
                        onClick={(e) => {
                            if (!this.isHeliosAndExistingClasseur() && !this.isFinalizedOrRetiredClasseur()) {
                                removeDocument(e, document.id)
                            }
                        }}>
                        <i className="fa fa-trash" style={{padding: '5px'}}/>
                    </div>
                </div>
            </div>)
        return (
            <div className="grid-x panel grid-padding-y">
                <div className="cell medium-12">
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="cell medium-12">
                            <h3>{`${t('common.documents.title_preview')} *`}</h3>
                        </div>
                    </div>
                    {(!this.isFinalizedOrRetiredClasseur() && !this.isPendingAndHeliosTypeCLasseur()) && this.props.editClasseur &&
                    <div className="grid-x grid-margin-x grid-padding-x grid-padding-y">
                        <div className="cell medium-12">
                            <Dropzone
                                className={
                                    `documentation-dropzone
                                        ${this.isHeliosAndHaveOneDocument() && 'disabled'}
                                        grid-x align-middle align-center`}
                                accept={accept}
                                multiple={multiple}
                                name="file"
                                disabled={disabled}
                                maxSize={104857600}
                                onDropRejected={(files) =>
                                    this.setState({dropFileError: fileRule})}
                                onDropAccepted={files => onDrop(files)}>
                                <Cell>
                                    <GridX className="align-center grid-margin-y grid-padding-y">
                                        <Cell>
                                            <i className="fa fa-file"/>
                                        </Cell>
                                    </GridX>
                                    <GridX className="align-center">
                                        <Cell className="medium-11 text-small">
                                            {(this.state.dropFileError) ?
                                                <span className="text-alert">{this.state.dropFileError}</span> :
                                                <span>{fileRule}</span>}
                                        </Cell>
                                    </GridX>
                                </Cell>
                            </Dropzone>
                        </div>
                    </div>}
                    <GridX className="grid-margin-x grid-padding-x grid-padding-y">
                        <Cell>
                            <div className="">
                                {docs}
                            </div>
                        </Cell>
                    </GridX>
                </div>
            </div>
        )
    }
}

DocumentsNew.contextTypes = {
    t: func
}

DocumentsNew.propTypes = {
    documents: array,
    onClick: func,
    onDrop: func,
    removeDocument: func,
    displayReveal: func,
    typeClasseur: object.isRequired,
    editClasseur: bool
}

export default translate('sesile')(DocumentsNew)