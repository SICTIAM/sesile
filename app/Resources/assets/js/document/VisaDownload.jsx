import React, {Component} from "react"
import {translate} from "react-i18next"
import {func} from 'prop-types'

import DraggablePosition from "../_components/DraggablePosition"

class DraggablePositionDownload extends Component {
    static contextTypes = {
        t: func
    }
    state = {
        imageFirstPage: '',
        imageLastPage: '',
        imageUrl: '',
        valid: false
    }

    isSignatureLastPage() {
        this.props.type === "signature" && this.props.collectivite.page_signature === 0 ?
            this.setState({imageUrl: `url(data:image/jpg;base64,${this.state.imageLastPage})`})
            :
            this.setState({imageUrl: `url(data:image/jpg;base64,${this.state.imageFirstPage})`})
    }

    componentWillReceiveProps(nextProps) {
        if (this.props.images !== nextProps.images) {
            this.setState({imageFirstPage: nextProps.images[0]})
            this.setState({imageLastPage: nextProps.images[1]})
            this.setState({valid: true})
        }
        if (this.state.valid) {
            this.isSignatureLastPage()
        }
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
                {this.props.orientation === "PORTRAIT" ?
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
                            background: this.state.imageUrl,
                            backgroundRepeat: 'no-repeat'

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
                </div> :
                    <div
                        style={{
                            textAlign: 'center',
                            padding: '1em',
                            width: '22em',
                            height: '18em',
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
                                height: '215px',
                                width: '320px',
                                position: 'relative',
                                overflow: 'auto',
                                padding: '0',
                                display: 'flex',
                                background: this.state.imageUrl,
                                backgroundRepeat: 'no-repeat'
                            }}
                            position={this.props.position}
                            boxStyle={{height: '30px', width: '65px', padding: 0}}
                            label={this.props.type}
                            bounds={{top:10, left: 10, right: 250, bottom: 180}}
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
                }
            </li>
        )
    }
}


DraggablePositionDownload.contextTypes = {
    t: func
}

export default translate('sesile')(DraggablePositionDownload)