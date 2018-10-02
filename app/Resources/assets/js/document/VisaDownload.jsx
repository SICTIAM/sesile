import DraggablePosition from "../_components/DraggablePosition";
import React, {Component} from "react";
import {translate} from "react-i18next";
import {array, func, string, object, bool} from 'prop-types'
import { handleErrors } from '../_utils/Utils'

class DraggablePositionDownload extends Component {
    static contextTypes = {
        t: func
    }
    state = {
        imageFirstPage: '',
        imageLastPage: '',
        imageurl: ''
    }

    fetchImage() {
        fetch(Routing.generate('sesile_document_documentapi_getpdfpreview', {id: this.props.id}),
            {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(images => this.setState({imageFirstPage: images[0], imageLastPage: images[1]}))
            .then(image => this.isSignatureLastPage())
    }

    isSignatureLastPage() {
        this.props.type === "signature" && this.props.collectivite.page_signature === 0 ?
            this.setState({imageurl: `url(data:image/jpg;base64,${this.state.imageLastPage})`})
            :
            this.setState({imageurl: `url(data:image/jpg;base64,${this.state.imageFirstPage})`})
        return (this.state.imageurl)
    }

    componentDidMount() {
        this.fetchImage()
    }

    render() {
        const {t} = this.context
        return (
            <li className="doc-action-button">
                <a
                    className={`button secondary clear ${this.props.disabled && ' disabled'}`}
                    data-toggle={!this.props.disabled && this.props.dataToggle}>
                    {this.props.label}
                </a>
                <div
                    style={{
                        textAlign: 'center',
                        padding: '1em',
                        width: '15em',
                        height: '23em',
                        marginLeft: '100px',
                        borderRadius: '5px'
                    }}
                    className="dropdown-pane"
                    id={this.props.dataToggle}
                    data-position="right"
                    data-alignment="center"
                    data-close-on-click={true}
                    data-dropdown data-auto-focus={true}>
                    <DraggablePosition
                        style={{
                            height: '300px',
                            width: '210px',
                            position: 'relative',
                            overflow: 'auto',
                            padding: '0',
                            display: 'flex',
                            background: this.state.imageurl
                        }}
                        position={this.props.position}
                        boxStyle={{height: '30px', width: '65px', padding: 0}}
                        label={this.props.type}
                        handleChange={this.props.handleChange}/>
                    <div>
                        <a
                            className="button secondary hollow"
                            href={this.props.href}
                            target="_blank">
                            {t('common.download')}
                        </a>
                    </div>
                </div>
            </li>
        )
    }
}


DraggablePositionDownload.contextTypes = {
    t: func
}

export default translate('sesile')(DraggablePositionDownload)