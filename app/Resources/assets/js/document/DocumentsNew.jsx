import React, { Component } from 'react'
import { translate } from 'react-i18next'
import Dropzone from 'react-dropzone'
import {array, func, string} from 'prop-types'
import DocumentPreview from './DocumentPreview'


class DocumentsNew extends Component {

    static contextTypes = {
        t: func
    }

    state = {
        disabled: true,
        accept: ''
    }

    componentWillReceiveProps(nextProps) {
        const { typeClasseur, documents } = nextProps

        if (typeClasseur.nom === "Helios" && documents.length > 0 ) {
            this.setState({disabled: true})
        }
        else {
            this.setState({disabled: false})
        }

        if (typeClasseur && typeClasseur.nom === "Helios") {
            this.setState({accept: 'text/xml'})
        } else {
            this.setState({accept: 'image/*, application/*, text/*'})
        }
    }

    render () {

        const { t } = this.context
        const { documents, onDrop, removeDocument }  = this.props
        const { disabled, accept } = this.state

        return (
            <div className="grid-x">
                <div className="cell medium-12">

                    <div className="grid-x">
                        <div className="cell medium-12">
                            <Dropzone onDrop={(e) => onDrop(e)}
                                      className="dropzone cell medium-12 text-center align-center-middle"
                                      disabled={disabled}
                                      accept={accept} >
                                <h4>{ t('common.documents.label_dropzone') }</h4>
                            </Dropzone>
                        </div>
                    </div>
                    <div className="grid-x">
                        <div className="cell medium-12">
                            <h4>Liste des documents</h4>
                        </div>
                    </div>
                    <div className="grid-x">
                        {
                            documents.map((document, key) =>
                                <DocumentPreview key={key}
                                                 document={document}
                                                 id={key}
                                                 removeDocument={removeDocument}
                                />
                            )
                        }
                    </div>

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
    displayReveal: func
}

export default translate('sesile')(DocumentsNew)