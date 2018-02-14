import React, { Component } from 'react'
import {func, object} from 'prop-types'
import HeliosInfos from './HeliosInfos'
import HeliosVoucher from './HeliosVoucher'
import HeliosPJs from './HeliosPJs'

class Helios extends Component {

    constructor(props) {
        super(props)
        this.state = {
            pes: {
                budget: "",
                signatory: "",
                dateSign: "",
                vouchers: []
            },
            voucher: {
                id: "",
                date_em: "",
                nb_piece: "",
                mt_bord_h_t: "",
                mt_cumul_annuel: "",
                exercice: "",
                type: "",
                list_pieces: []
            },
            revealDisplay: "none",
            pj: "",
            pageNumber: 1,
        }
    }

    static propTypes = {
        document: object.isRequired
    }

    static contextTypes = { t: func }

    componentDidMount () {
        fetch(Routing.generate("sesile_document_documentapi_helios", {id: this.props.document.id}), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(pes => this.setState({pes: pes, voucher: pes.vouchers[0]}))
    }

    handleChangeVouchers = (name, value) => this.setState(prevState => prevState.voucher = this.state.pes.vouchers.find(voucher => voucher.id == value))
    handleClickPJ = (name, value) => {
        const pj = this.state.voucher.list_pieces.find(piece => piece.liste_p_js.find(pj => pj.id == value)).liste_p_js.find(pj => pj.id == value)
        this.setState({pj: Routing.generate("sesile_document_documentapi_getpj", {id: this.props.document.id, pejid: pj.id, pejname: pj.nom}, true), revealDisplay: 'block'})
    }

    hideRevealDisplay = () => this.setState({revealDisplay: 'none'})

    render () {
        const { pes, voucher, pj, revealDisplay } = this.state

        return (
            <div className="cell medium-9 text-left cell-block-y" key={this.props.document.id}>
                {
                    pj &&
                    <div className="reveal-full" style={{display: revealDisplay}}>
                        <div className="fi-x reveal-ico" onClick={() => this.hideRevealDisplay()}></div>
                        <object data={pj} type="application/pdf" width="100%" height="100%"></object>
                    </div>
                }

                 <div>
                    <HeliosInfos pes={pes}
                                 voucher={voucher}
                                 handleChangeVouchers={this.handleChangeVouchers}
                    />

                    <HeliosVoucher voucher={voucher} />
                    <HeliosPJs pjs={voucher.list_pieces} handleClickPJ={this.handleClickPJ} />
                </div>

            </div>
        )
    }
}

export default Helios