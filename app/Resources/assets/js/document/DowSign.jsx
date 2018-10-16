import DraggablePositionDownload from "./VisaDownload";
import DraggablePositionVisaSignatureDownload from "./VisaSignatureDownload";
import React, {Component} from "react";
import {translate} from "react-i18next";
import {func} from "prop-types";
import {handleErrors} from "../_utils/Utils";

class DowSign extends Component {
    static contextTypes = {
        t: func
    }
    state = {
        images: [],
        collectivite: {}
    }

    componentDidMount() {
        this.fetchImage()
    }
    componentWillReceiveProps(nextProps) {
        if (this.props.collectivite !== nextProps.collectivite) {
            this.setState({collectivite: nextProps.collectivite})
        }
    }

    fetchImage() {
        fetch(Routing.generate('sesile_document_documentapi_getpdfpreview', {id: this.props.document.id}),
            {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(images => this.setState({images: images}))
            .then(image => this.setState({valid: true}))
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
    isNewDocument = (document) => !document.id
    userNotHaveSignatureImage = () => this.props.user.path_signature && this.props.user.path_signature.trim() !== ""

    render() {
        const {t} = this.context
        const { document, user} = this.props
        const {collectivite} = this.state
        return (
                <div
                    title={t('common.download')}
                    className={
                        `cell medium-auto align-center
                                doc-action-button
                                ${this.isNewDocument(document) && ' disabled'}`}
                    style={{display: 'flex', borderLeft: 'solid 1px #b3b2b2'}}>
                    <a
                        style={{
                            color: '#404257',
                            width: '100%',
                            textAlign: 'center'
                        }}
                        data-toggle={`document-dropdown-${document.id}`}>
                        <i className="fa fa-download" style={{padding: '5px'}}/>
                    </a>
                    <div
                        style={{
                            textAlign: 'center',
                            padding: 0,
                            width: '10em',
                            borderRadius: '5px',
                            marginTop: '5px'
                        }}
                        className="dropdown-pane"
                        id={`document-dropdown-${document.id}`}
                        data-alignment="center"
                        data-hover="true" data-hover-pane="true"
                        data-dropdown>
                        <ul className="no-bullet" style={{marginBottom: 0}}>
                            <hr style={{margin: 0}}/>
                            <DraggablePositionDownload
                                images={this.state.images}
                                href={Routing.generate('download_doc_visa', {
                                    orgId: user.current_org_id,
                                    id: document.id,
                                    absVisa: collectivite.abscisses_visa,
                                    ordVisa: collectivite.ordonnees_visa
                                })}
                                position={{
                                    x: collectivite.abscisses_visa,
                                    y: collectivite.ordonnees_visa
                                }}
                                label={t('common.documents.btn_visa')}
                                handleChange={this.handleChangeVisaPosition}
                                collectivite={collectivite}
                                dataToggle={`visa-dropdown-${document.id}`}
                                type="visa"
                                id={    document.id}
                                disabled={false}/>
                            <hr style={{margin: 0}}/>
                            <div>
                                <DraggablePositionDownload
                                    images={this.state.images}
                                    href={
                                        Routing.generate(
                                            'download_doc_sign',
                                            {
                                                orgId: user.current_org_id,
                                                id: document.id,
                                                absSign: collectivite.abscisses_signature,
                                                ordSign: collectivite.ordonnees_signature
                                            })}
                                    position={{
                                        x: collectivite.abscisses_signature,
                                        y: collectivite.ordonnees_signature
                                    }}
                                    label={t('common.documents.btn_signature')}
                                    handleChange={this.handleChangeSignaturePosition}
                                    collectivite={collectivite}
                                    dataToggle={`signature-dropdown-${document.id}`}
                                    type="signature"
                                    id={document.id}
                                    disabled={!this.userNotHaveSignatureImage()}/>
                                <hr style={{margin: 0}}/>
                            </div>
                            <div>
                                <DraggablePositionVisaSignatureDownload
                                    images={this.state.images}
                                    href={
                                        Routing.generate(
                                            'download_doc_all',
                                            {
                                                orgId: user.current_org_id,
                                                id: document.id,
                                                absSign: collectivite.abscisses_signature,
                                                ordSign: collectivite.ordonnees_signature,
                                                absVisa: collectivite.abscisses_visa,
                                                ordVisa: collectivite.ordonnees_visa
                                            })}
                                    positionSignature={{
                                        x: collectivite.abscisses_signature,
                                        y: collectivite.ordonnees_signature
                                    }}
                                    positionVisa={{
                                        x: collectivite.abscisses_visa,
                                        y: collectivite.ordonnees_visa
                                    }}
                                    label={t('common.documents.btn_both')}
                                    handleChangeSignature={this.handleChangeSignaturePosition}
                                    handleChangeVisa={this.handleChangeVisaPosition}
                                    collectivite={collectivite}
                                    dataToggle={`signature-visa-dropdown-${document.id}`}
                                    type="signature"
                                    id={document.id}
                                    disabled={!this.userNotHaveSignatureImage()}/>
                                <hr style={{margin: 0}}/>
                            </div>
                            <li className="doc-action-button">
                                <a
                                    className="button secondary clear"
                                    href={Routing.generate('download_doc', {id: document.id})}>
                                    {t('common.electronic_signature')}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
        )
    }
}

DowSign.contextTypes = {
    t: func
}

export default translate('sesile')(DowSign)