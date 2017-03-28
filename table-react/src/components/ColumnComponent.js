import React from 'react';

class Column extends React.Component {
	render() {
		return (
			<th>{this.props.value}</th>
		);
	}
}

export default Column;